<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{

protected $table = 'pesan';
protected $guarded = [];
    // Mengizinkan kolom ini diisi secara massal (mass assignment)
    protected $fillable = [
        'peminjaman_id', 
        'pengirim_id', 
        'isi_pesan',
        'sudah_dibaca', // Menandai apakah pesan sudah dibaca atau belum
    ];

    // Relasi: Pesan ini milik siapa?
    public function sender()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }
    
    // Relasi: Pesan ini ada di transaksi peminjaman mana?
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }
     public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }
}