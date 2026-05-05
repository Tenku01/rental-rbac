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

        // Meskipun channel publik, akses halaman tetap dibatasi role
        if (!$user || !$user->hasAnyRole(['admin', 'resepsionis'])) {
            abort(403, 'Akses Ditolak.');
        }

        $this->loadSessions();
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

    /**
     * PERUBAHAN LISTENER:
     * Dari 'echo-private' menjadi 'echo' karena menggunakan Public Channel.
     * Nama channel: admin-channel
     * Nama event: .MessageToAdminEvent (titik diperlukan karena broadcastAs)
     */
    #[On('echo:admin-channel,.MessageToAdminEvent')]
    public function handleIncomingMessage($payload)
    {
        if (!in_array($payload['session_id'], $this->chatSessions)) {
            $this->loadSessions();
        }

        if (trim($this->activeSessionId) === trim($payload['session_id'])) {
            $this->messages[] = [
                'id' => $payload['id'],
                'session_id' => $payload['session_id'],
                'isi_pesan' => $payload['isi_pesan'],
                'pengirim_id' => $payload['pengirim_id'],
                'waktu' => $payload['waktu'],
            ];
            
            $this->dispatch('scroll-to-bottom'); 
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

        broadcast(new GuestMessageEvent($pesan))->toOthers();

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