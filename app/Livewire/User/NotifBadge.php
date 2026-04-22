<?php

namespace App\Livewire\User;

use Livewire\Component;
use App\Models\Peminjaman;
use App\Models\Pesan;
use Illuminate\Support\Facades\Auth;

class NotifBadge extends Component
{
    public $totalUnreadAll = 0;
    public $chatList = [];
    public $activeChatIds = [];

    public function mount()
    {
        $this->loadData();
    }

    // Fungsi ini dipanggil otomatis oleh Alpine.js saat ada pesan masuk dari WebSocket
    public function loadData()
    {
        $userId = Auth::id();

        // 1. Ambil pesanan milik user ini yang sedang aktif
        $activeChats = Peminjaman::with(['mobil'])
            ->where('user_id', $userId)
            ->whereIn('status', ['pembayaran dp', 'sudah dibayar lunas', 'berlangsung'])
            ->get();

        // Simpan ID untuk didengarkan oleh Echo (Javascript)
        $this->activeChatIds = $activeChats->pluck('id')->values()->toArray();

        $tempChatList = [];
        $totalUnread = 0;

        foreach($activeChats as $chat) {
            $lm = Pesan::where('peminjaman_id', $chat->id)->latest()->first();
            
            $unreadCount = Pesan::where('peminjaman_id', $chat->id)
                ->where('pengirim_id', '!=', $userId)
                ->where('sudah_dibaca', false)
                ->count();
                
            $tempChatList[] = [
                'id' => $chat->id,
                'mobil_merek' => $chat->mobil->merek ?? 'Mobil',
                'last_message' => $lm ? $lm->isi_pesan : 'Mulai percakapan...',
                'last_time' => $lm ? $lm->created_at : $chat->created_at,
                'unread' => $unreadCount
            ];

            $totalUnread += $unreadCount;
        }

        // Urutkan: Pesan belum dibaca paling atas, lalu berdasarkan waktu terbaru
        usort($tempChatList, function($a, $b) {
            if ($a['unread'] !== $b['unread']) {
                return $b['unread'] <=> $a['unread'];
            }
            return $b['last_time'] <=> $a['last_time'];
        });

        // Ambil 10 pesan teratas saja untuk dropdown
        $this->chatList = array_slice($tempChatList, 0, 10);
        $this->totalUnreadAll = $totalUnread;
    }

    // Fungsi untuk menandai semua pesan sebagai dibaca
    public function markAsRead()
    {
        $userId = Auth::id();
        $activeChats = Peminjaman::where('user_id', $userId)
            ->whereIn('status', ['pembayaran dp', 'sudah dibayar lunas', 'berlangsung'])
            ->pluck('id');

        Pesan::whereIn('peminjaman_id', $activeChats)
            ->where('pengirim_id', '!=', $userId)
            ->where('sudah_dibaca', false)
            ->update(['sudah_dibaca' => true]);

        // Muat ulang data agar badge merah hilang seketika
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.user.notif-badge');
    }
}