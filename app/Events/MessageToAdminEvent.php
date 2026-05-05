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
        /**
         * Diubah ke PrivateChannel agar sesuai dengan auth di channels.php
         * Nama channel disamakan: admin.guest-chat
         */
        return [new PrivateChannel('admin.guest-chat')];
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