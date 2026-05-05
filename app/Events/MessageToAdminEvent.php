<?php

namespace App\Events;

use App\Models\Pesan;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageToAdminEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $pesan;

    public function __construct(Pesan $pesan)
    {
        $this->pesan = $pesan;
    }

    public function broadcastOn(): array
    {
        // Harus sesuai dengan auth di channels.php
        return [new PrivateChannel('admin.guest-chat')];
    }

    /**
     * SANGAT PENTING: Menentukan nama event secara eksplisit.
     * Tanpa ini, Laravel akan mengirim nama dengan namespace lengkap (App\Events\...)
     * yang seringkali sulit dideteksi oleh listener Livewire di VPS.
     */
    public function broadcastAs(): string
    {
        return 'MessageToAdminEvent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->pesan->id,
            'session_id' => $this->pesan->session_id,
            'isi_pesan' => $this->pesan->isi_pesan,
            'pengirim_id' => $this->pesan->pengirim_id,
            'waktu' => $this->pesan->created_at->format('H:i'),
        ];
    }
}