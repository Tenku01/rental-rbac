<?php

namespace App\Events;

use App\Models\Pesan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GuestMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $pesan;

    public function __construct(Pesan $pesan)
    {
        $this->pesan = $pesan;
    }

    /**
     * Tentukan channel tempat pesan disiarkan.
     * Karena fokus di pelanggan dulu, kita gunakan Channel Publik berdasarkan session_id.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('guest-chat.' . $this->pesan->session_id),
        ];
    }

    /**
     * Data yang akan dikirimkan ke frontend (Alpine.js)
     */
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