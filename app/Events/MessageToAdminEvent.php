<?php

namespace App\Events;

use App\Models\Pesan;
use Illuminate\Broadcasting\Channel;
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

    /**
     * MENGGUNAKAN PUBLIC CHANNEL
     * Menggunakan 'Channel' (bukan PrivateChannel) agar tidak memerlukan otentikasi.
     */
    public function broadcastOn(): array
    {
        return [new Channel('admin-channel')];
    }

    /**
     * Nama event yang akan didengar oleh Livewire.
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