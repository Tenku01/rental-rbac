<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembatalanPesanan extends Model
{
    protected $table = 'pembatalan_pesanan';
    
    protected $fillable = [
        'peminjaman_id',
        'user_id',
        'dibatalkan_oleh',          // 🔹 Diperbarui dari cancelled_by
        'alasan',
        'status_pengembalian_dana', // 🔹 Diperbarui dari refund_status
        'dibatalkan_pada',          // 🔹 Diperbarui dari cancelled_at
        'status_persetujuan',       // 🔹 Diperbarui dari approval_status
        'persentase_refund',
        'jumlah_refund',
        'id_transaksi_refund'
    ];

    protected $casts = [
        'dibatalkan_pada' => 'datetime', // 🔹 Diperbarui dari cancelled_at
    ];

    public function peminjaman()
    { 
        return $this->belongsTo(Peminjaman::class); 
    }
    
    public function user()
    { 
        return $this->belongsTo(User::class); 
    }
    
}