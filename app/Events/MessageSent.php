<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Tentukan "Ruangan" (Room) mana pesan ini akan disiarkan.
     */
    public function broadcastOn(): array
    {
        // Kita gunakan ID peminjaman sebagai nama ruangan yang bersifat Private
        return [
            new PrivateChannel('chat.' . $this->message->peminjaman_id),
        ];
    }

    /**
     * Data apa saja yang akan dikirim ke Javascript/Livewire di frontend.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'peminjaman_id' => $this->message->peminjaman_id,
            'sender_id' => $this->message->sender_id,
            'message' => $this->message->message,
            'waktu' => $this->message->created_at->format('H:i'),
        ];
    }
}
