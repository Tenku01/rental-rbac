<?php

namespace App\Events;

use App\Models\Pesan; // 🔹 Wajib diubah menjadi Pesan
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $pesan;

    public function __construct(Pesan $pesan)
    {
        $this->pesan = $pesan;
    }

    /**
     * Tentukan "Ruangan" (Room) mana pesan ini akan disiarkan.
     */
    public function broadcastOn(): array
    {
        // Kita gunakan ID peminjaman sebagai nama ruangan yang bersifat Private
        return [
            new PrivateChannel('chat.' . $this->pesan->peminjaman_id),
        ];
    }

    /**
     * Data apa saja yang akan dikirim ke Javascript/Livewire di frontend.
     */
    public function broadcastWith(): array
    {
        // 🔹 Diperbarui: Gunakan $this->pesan dan panggil nama kolom bahasa Indonesia
        return [
            'id'            => $this->pesan->id,
            'peminjaman_id' => $this->pesan->peminjaman_id,
            'pengirim_id'   => $this->pesan->pengirim_id,
            'isi_pesan'     => $this->pesan->isi_pesan,
            'waktu'         => $this->pesan->created_at->format('H:i'),
        ];
    }
}