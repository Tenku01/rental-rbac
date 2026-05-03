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

    // Menggunakan layout admin Anda
    #[Layout('layouts.admin')]
    public function mount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // PERMISSION: Hanya izinkan Admin dan Resepsionis
        if (!$user || !$user->hasAnyRole(['admin', 'resepsionis'])) {
            abort(403, 'Akses Ditolak: Fitur Livechat hanya untuk Admin dan Resepsionis.');
        }

        $this->loadSessions();
    }

    public function loadSessions()
    {
        // Ambil daftar Guest (session_id) yang pernah chat
        $this->chatSessions = Pesan::whereNotNull('session_id')
            ->select('session_id')
            ->distinct()
            ->pluck('session_id')
            ->toArray();
    }

    public function selectChat($sessionId)
    {
        $this->activeSessionId = $sessionId;
        $this->loadMessages();
        
        // Memaksa scroll ke bawah saat chat diklik (menunggu DOM update)
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

    // MENERIMA PESAN REAL-TIME DARI GUEST
    // Mendengarkan channel publik khusus Admin menggunakan MessageToAdminEvent
    #[On('echo:admin-channel,MessageToAdminEvent')]
    public function handleIncomingMessage($payload)
    {
        // Jika ada guest baru (belum ada di daftar sidebar), update daftar
        if (!in_array($payload['session_id'], $this->chatSessions)) {
            $this->loadSessions();
        }

        // Jika pesan masuk ke ruang obrolan guest yang SEDANG DIBUKA oleh Admin
        if ($this->activeSessionId === $payload['session_id']) {
            $this->messages[] = $payload; // Tambah pesan secara reaktif
            
            // Beri perintah ke browser untuk scroll ke bawah
            $this->dispatch('scroll-to-bottom'); 
        }
    }

    // MENGIRIM PESAN BALASAN KE GUEST
    public function sendMessage()
    {
        $this->validate(['newMessage' => 'required|string']);
        if (!$this->activeSessionId) return;

        // 1. Simpan ke database
        $pesan = new Pesan();
        $pesan->session_id = $this->activeSessionId;
        $pesan->isi_pesan = $this->newMessage;
        $pesan->pengirim_id = Auth::id(); // Terisi ID Resepsionis yang membalas
        $pesan->peminjaman_id = null;
        $pesan->save();

        // 2. Tembakkan event Reverb untuk dikirim KHUSUS ke Guest tersebut
        // Menggunakan event yang mengirim ke public channel 'guest-chat.{session_id}'
        broadcast(new GuestMessageEvent($pesan))->toOthers();

        // 3. Tampilkan pesan kita sendiri di layar tanpa menunggu server reload
        $this->messages[] = [
            'id' => $pesan->id,
            'session_id' => $pesan->session_id,
            'isi_pesan' => $pesan->isi_pesan,
            'pengirim_id' => $pesan->pengirim_id,
            'waktu' => $pesan->created_at->format('H:i'),
        ];
        
        // Bersihkan inputan
        $this->newMessage = '';
        
        // Beri perintah ke browser untuk scroll ke bawah
        $this->dispatch('scroll-to-bottom');
    }

    public function render()
    {
        return view('livewire.resepsionis.live-chat-admin');
    }
}