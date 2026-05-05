<?php

namespace App\Livewire\Resepsionis;

use Livewire\Component;
use App\Models\Pesan;
use App\Events\GuestMessageEvent;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

class LiveChatAdmin extends Component
{
    public $activeSessionId = null;
    public $chatSessions = [];
    public $messages = [];
    public $newMessage = '';
    public $lastMessageCount = 0;

    #[Layout('layouts.admin')]
    public function mount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || !$user->hasAnyRole(['admin', 'resepsionis'])) {
            abort(403, 'Akses Ditolak.');
        }

        $this->loadSessions();
    }

    /**
     * Fungsi yang akan dipanggil secara berkala oleh wire:poll
     */
    public function refreshData()
    {
        // 1. Refresh daftar sesi di sidebar
        $this->loadSessions();

        // 2. Jika ada chat yang dibuka, refresh pesannya
        if ($this->activeSessionId) {
            $currentCount = count($this->messages);
            $this->loadMessages();
            
            // Jika ada pesan baru masuk, trigger scroll ke bawah
            if (count($this->messages) > $currentCount) {
                $this->dispatch('scroll-to-bottom');
            }
        }
    }

    public function loadSessions()
    {
        $this->chatSessions = Pesan::whereNotNull('session_id')
            ->select('session_id')
            ->distinct()
            ->latest('created_at')
            ->pluck('session_id')
            ->toArray();
    }

    public function selectChat($sessionId)
    {
        $this->activeSessionId = $sessionId;
        $this->loadMessages();
        $this->dispatch('scroll-to-bottom');
    }

    public function loadMessages()
    {
        if ($this->activeSessionId) {
            $this->messages = Pesan::where('session_id', $this->activeSessionId)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($msg) {
                    return [
                        'id' => $msg->id,
                        'session_id' => $msg->session_id,
                        'isi_pesan' => $msg->isi_pesan,
                        'pengirim_id' => $msg->pengirim_id,
                        'waktu' => $msg->created_at->format('H:i'),
                    ];
                })
                ->toArray();
        }
    }

    public function sendMessage()
    {
        $this->validate(['newMessage' => 'required|string']);
        if (!$this->activeSessionId) return;

        $pesan = new Pesan();
        $pesan->session_id = $this->activeSessionId;
        $pesan->isi_pesan = $this->newMessage;
        $pesan->pengirim_id = Auth::id();
        $pesan->save();

        // Tetap broadcast agar Guest (sisi pelanggan) tetap bisa realtime jika mereka pakai Reverb
        broadcast(new GuestMessageEvent($pesan))->toOthers();

        $this->newMessage = '';
        $this->loadMessages(); // Refresh langsung setelah kirim
        $this->dispatch('scroll-to-bottom');
    }

    public function render()
    {
        return view('livewire.resepsionis.live-chat-admin');
    }
}