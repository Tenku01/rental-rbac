<?php

namespace App\Livewire\Sopir;

use Livewire\Component;
use App\Models\Pesan; // 🔹 Diperbarui dari Message
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
        // 🔹 Diperbarui: Message -> Pesan, sender_id -> pengirim_id, is_read -> sudah_dibaca
        Pesan::where('peminjaman_id', $this->peminjaman_id)
                ->where('pengirim_id', '!=', Auth::id())
                ->where('sudah_dibaca', false)
                ->update(['sudah_dibaca' => true]);

        // 2. AMBIL data obrolan dan simpan ke variabel array $riwayatChat
        // 🔹 Diperbarui: Message -> Pesan
        $this->riwayatChat = Pesan::where('peminjaman_id', $this->peminjaman_id)
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
        // 🔹 Diperbarui: Message -> Pesan, sender_id -> pengirim_id, message -> isi_pesan
        $message = Pesan::create([
            'peminjaman_id' => $this->peminjaman_id,
            'pengirim_id' => Auth::id(),
            'isi_pesan' => $this->pesanBaru,
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
        // 🔹 Diperbarui: sender_id -> pengirim_id
        if ($payload['pengirim_id'] !== Auth::id()) {
            $this->riwayatChat[] = $payload;
        }
    }

    public function render()
    {
        return view('livewire.sopir.chat-peminjaman');
    }
}