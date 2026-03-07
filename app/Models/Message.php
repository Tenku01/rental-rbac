<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    // Mengizinkan kolom ini diisi secara massal (mass assignment)
    protected $fillable = [
        'peminjaman_id', 
        'sender_id', 
        'message',
        'is_read', // Menandai apakah pesan sudah dibaca atau belum
    ];

    // Relasi: Pesan ini milik siapa?
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    
    // Relasi: Pesan ini ada di transaksi peminjaman mana?
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }
}