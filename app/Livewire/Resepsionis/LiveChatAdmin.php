<?php

namespace App\Livewire\Resepsionis;

use Livewire\Component;
use App\Models\Pesan;
use App\Events\GuestMessageEvent;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

class LiveChatAdmin extends Component
{
    public $activeSessionId = null;
    public $chatSessions = [];
    public $messages = [];
    public $newMessage = '';

    #[Layout('layouts.admin')]
    public function mount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Keamanan: Hanya izinkan Admin dan Resepsionis
        if (!$user || !$user->hasAnyRole(['admin', 'resepsionis'])) {
            abort(403, 'Akses Ditolak: Fitur Livechat hanya untuk Admin dan Resepsionis.');
        }

        $this->loadSessions();
    }

    public function loadSessions()
    {
        // Ambil daftar sesi chat unik
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
        
        // Scroll ke bawah setelah memilih chat
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

    /**
     * MENERIMA PESAN REAL-TIME DARI GUEST
     * Menggunakan Private Channel 'admin.guest-chat' sesuai dengan channels.php
     * Pastikan MessageToAdminEvent melakukan broadcastOn ke PrivateChannel('admin.guest-chat')
     */
    #[On('echo-private:admin.guest-chat,MessageToAdminEvent')]
    public function handleIncomingMessage($payload)
    {
        // Debugging payload jika perlu: \Log::info($payload);

        // 1. Jika ada sesi baru, refresh daftar sidebar
        if (!in_array($payload['session_id'], $this->chatSessions)) {
            $this->loadSessions();
        }

        // 2. Jika pesan masuk untuk chat yang sedang dibuka, tambahkan ke array messages
        if ($this->activeSessionId === $payload['session_id']) {
            $this->messages[] = [
                'id' => $payload['id'] ?? rand(1000, 9999),
                'session_id' => $payload['session_id'],
                'isi_pesan' => $payload['isi_pesan'],
                'pengirim_id' => $payload['pengirim_id'] ?? null,
                'waktu' => now()->format('H:i'),
            ];
            
            $this->dispatch('scroll-to-bottom'); 
        }
    }

    public function sendMessage()
    {
        $this->validate(['newMessage' => 'required|string']);
        if (!$this->activeSessionId) return;

        // Simpan ke DB
        $pesan = new Pesan();
        $pesan->session_id = $this->activeSessionId;
        $pesan->isi_pesan = $this->newMessage;
        $pesan->pengirim_id = Auth::id();
        $pesan->save();

        // Broadcast ke channel Guest
        broadcast(new GuestMessageEvent($pesan))->toOthers();

        // Update UI Admin sendiri secara instan
        $this->messages[] = [
            'id' => $pesan->id,
            'session_id' => $pesan->session_id,
            'isi_pesan' => $pesan->isi_pesan,
            'pengirim_id' => $pesan->pengirim_id,
            'waktu' => $pesan->created_at->format('H:i'),
        ];
        
        $this->newMessage = '';
        $this->dispatch('scroll-to-bottom');
    }

    public function render()
    {
        return view('livewire.resepsionis.live-chat-admin');
    }
}