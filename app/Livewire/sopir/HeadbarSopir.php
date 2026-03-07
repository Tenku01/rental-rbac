<?php

namespace App\Livewire\Sopir;

use Livewire\Component;
use App\Models\Sopir;
use App\Models\Peminjaman;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class HeadbarSopir extends Component
{
    public $totalUnreadAll = 0;
    public $chatList = [];
    public $activeChatIds = [];

    public function mount()
    {
        $this->loadData();
    }

    // Fungsi ini bisa dipanggil kapan saja oleh Javascript untuk menyegarkan data!
    public function loadData()
    {
        $sopir = Sopir::where('user_id', Auth::id())->first();
        $sopirId = $sopir ? $sopir->id : 0;

        $activeChats = $sopirId ? Peminjaman::with(['mobil', 'user'])
                            ->where('sopir_id', $sopirId)
                            ->whereIn('status', ['pembayaran dp', 'sudah dibayar lunas', 'berlangsung'])
                            ->get() : collect([]);

        // Simpan ID untuk didengarkan oleh Echo (Javascript)
        $this->activeChatIds = $activeChats->pluck('id')->values()->toArray();

        $tempChatList = [];
        $totalUnread = 0;

        foreach($activeChats as $chat) {
            $lm = Message::where('peminjaman_id', $chat->id)->latest()->first();
            
            $unreadCount = Message::where('peminjaman_id', $chat->id)
                ->where('sender_id', '!=', Auth::id())
                ->where('is_read', false)
                ->count();
                
            // Flatten data agar mudah dirender oleh Blade
            $tempChatList[] = [
                'id' => $chat->id,
                'mobil_merek' => $chat->mobil->merek ?? 'Mobil',
                'user_name' => $chat->user->name ?? 'Pelanggan',
                'last_message' => $lm ? $lm->message : 'Belum ada obrolan...',
                'last_time' => $lm ? $lm->created_at : $chat->created_at,
                'unread' => $unreadCount
            ];

            $totalUnread += $unreadCount;
        }

        // Urutkan (Belum dibaca di atas, lalu berdasarkan waktu terbaru)
        usort($tempChatList, function($a, $b) {
            if ($a['unread'] !== $b['unread']) {
                return $b['unread'] <=> $a['unread'];
            }
            return $b['last_time'] <=> $a['last_time'];
        });

        $this->chatList = $tempChatList;
        $this->totalUnreadAll = $totalUnread;
    }

    public function render()
    {
        return view('livewire.sopir.headbar-sopir');
    }
}