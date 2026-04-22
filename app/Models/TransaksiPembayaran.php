<?php
// app/Models/PaymentTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiPembayaran extends Model
{
    protected $table = 'transaksi_pembayaran';
    use HasFactory;

   protected $fillable = [
    'peminjaman_id', 'id_transaksi_midtrans', 'status', 'jumlah', 'tipe_transaksi', 'respon_midtrans','id_transaksi_awal'
];


    /**
     * Relasi ke tabel peminjaman
     */
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class);
    }
}
