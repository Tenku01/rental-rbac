<?php

namespace App\Livewire\Sopir;

use Livewire\Component;
use App\Models\Message;
use App\Events\MessageSent;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout; // 1. Import Attribute Layout
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.sopir')] // 2. Beri tahu Livewire untuk menggunakan layouts/sopir.blade.php
class ChatPeminjaman extends Component
{
    public $peminjaman_id;
    public $pesanBaru = '';
    public $riwayatChat = [];

public function mount($peminjaman_id)
    {
        $this->peminjaman_id = $peminjaman_id;
        
        // 1. UPDATE status baca secara terpisah (TIDAK ditampung ke variabel)
        Message::where('peminjaman_id', $this->peminjaman_id)
                ->where('sender_id', '!=', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);

        // 2. AMBIL data obrolan dan simpan ke variabel array $riwayatChat
        $this->riwayatChat = Message::where('peminjaman_id', $this->peminjaman_id)
                                    ->orderBy('created_at', 'asc')
                                    ->get()
                                    ->toArray();
    }

    public function kirimPesan()
    {
        $this->validate([
            'pesanBaru' => 'required|string'
        ]);

        // 1. Simpan pesan ke database
        $message = Message::create([
            'peminjaman_id' => $this->peminjaman_id,
            'sender_id' => Auth::id(),
            'message' => $this->pesanBaru,
        ]);

        // 2. Siarkan (Broadcast) pesan ke server Reverb
        broadcast(new MessageSent($message));

        // 3. Tambahkan pesan ke layar sopir itu sendiri
        $this->riwayatChat[] = $message->toArray();
        
        // 4. Kosongkan input form
        $this->pesanBaru = ''; 
    }

    // Mendengarkan pesan masuk dari Pelanggan via channel Reverb
    #[On('echo-private:chat.{peminjaman_id},MessageSent')]
    public function pesanMasuk($payload)
    {
        // Hanya tambahkan ke layar jika yang mengirim BUKAN diri sendiri
        if ($payload['sender_id'] !== Auth::id()) {
            $this->riwayatChat[] = $payload;
        }
    }

    public function render()
    {
        return view('livewire.sopir.chat-peminjaman');
    }
}