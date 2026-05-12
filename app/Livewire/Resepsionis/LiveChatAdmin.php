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
    public $activeTab = 'semua'; // Tab aktif: semua, belum_dibaca, sudah_dibaca

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
     * Mengatur tab yang aktif
     */
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    /**
     * Mendapatkan sesi yang sudah difilter berdasarkan tab
     */
    public function getFilteredSessionsProperty()
    {
        return array_filter($this->chatSessions, function ($session) {
            if ($this->activeTab === 'belum_dibaca') {
                return $session['unread_count'] > 0;
            }
            if ($this->activeTab === 'sudah_dibaca') {
                return $session['unread_count'] == 0;
            }
            return true; // Untuk tab 'semua'
        });
    }

    /**
     * Fungsi Polling (3 detik sekali)
     */
    public function refreshData()
    {
        $this->loadSessions();

        if ($this->activeSessionId) {
            $currentCount = count($this->messages);
            
            // Tandai pesan baru dari guest sebagai sudah dibaca jika room sedang dibuka
            $this->markAsRead($this->activeSessionId);
            
            $this->loadMessages();
            
            if (count($this->messages) > $currentCount) {
                $this->dispatch('scroll-to-bottom');
            }
        }
    }

    public function loadSessions()
    {
        // Ambil session_id unik dan hitung pesan belum dibaca (yang dikirim guest: pengirim_id = null)
        $this->chatSessions = Pesan::whereNotNull('session_id')
            ->select('session_id')
            ->selectRaw('MAX(created_at) as last_chat')
            ->selectRaw('SUM(CASE WHEN sudah_dibaca = 0 AND pengirim_id IS NULL THEN 1 ELSE 0 END) as unread_count')
            ->groupBy('session_id')
            ->orderBy('last_chat', 'desc')
            ->get()
            ->toArray();
    }

    public function selectChat($sessionId)
    {
        $this->activeSessionId = $sessionId;
        
        // Tandai sebagai dibaca saat chat diklik
        $this->markAsRead($sessionId);
        
        $this->loadMessages();
        $this->dispatch('scroll-to-bottom');
    }

    protected function markAsRead($sessionId)
    {
        // Update pesan dari guest (pengirim_id is null) menjadi sudah dibaca (1)
        Pesan::where('session_id', $sessionId)
            ->where('pengirim_id', null)
            ->where('sudah_dibaca', 0)
            ->update(['sudah_dibaca' => 1]);
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
                        'sudah_dibaca' => $msg->sudah_dibaca,
                        'waktu' => $msg->created_at->format('l, H:i'),,
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
        $pesan->sudah_dibaca = 1; // Pesan admin otomatis sudah dibaca
        $pesan->save();

        // Tetap kirim broadcast untuk Guest (jika mereka masih pakai Reverb)
        broadcast(new GuestMessageEvent($pesan))->toOthers();

        $this->newMessage = '';
        $this->loadMessages();
        $this->dispatch('scroll-to-bottom');
    }

    public function render()
    {
        return view('livewire.resepsionis.live-chat-admin', [
            'filteredSessions' => $this->filteredSessions
        ]);
    }
}