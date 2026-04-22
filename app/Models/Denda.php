<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    use HasFactory;

    protected $table = 'denda';

    protected $fillable = [
        'peminjaman_id',
        'denda_keterlambatan',
        'denda_kerusakan',
        'total_denda',
        'status',
        'metode_pembayaran',
        'tanggal_pembayaran',
        'tanggal_terdeteksi',
        'keterangan'
    ];

    /**
     * Casting tipe data agar otomatis formatnya benar saat diakses
     */
    protected $casts = [
        'denda_keterlambatan' => 'decimal:2',
        'denda_kerusakan' => 'decimal:2',
        'total_denda' => 'decimal:2',
        'tanggal_terdeteksi' => 'date',
        'tanggal_pembayaran' => 'datetime',
    ];

    /**
     * Relasi ke tabel peminjaman
     */
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class);
    }
}