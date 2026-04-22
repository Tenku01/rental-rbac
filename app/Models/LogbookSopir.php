<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogbookSopir extends Model
{
    use HasFactory;

    // 🔹 Nama tabel di database disesuaikan dengan struktur baru
    protected $table = 'logbook_sopir';

    /**
     * MATIKAN TIMESTAMPS OTOMATIS
     * Karena tabel Anda tidak memiliki kolom 'created_at' dan 'updated_at',
     * properti ini wajib diset ke false agar tidak muncul error 'Column not found'.
     */
    public $timestamps = false;

    // Kolom yang dapat diisi secara massal (mass assignable)
    protected $fillable = [
        'peminjaman_id',
        'tanggal_aktivitas',
        'waktu_log', // Tambahkan ini agar bisa disimpan lewat Controller
        'deskripsi_aktivitas',
        'status_log',
        'foto_bukti',
    ];

    // Casting untuk mengkonversi tipe data dari database ke tipe PHP yang sesuai
    protected $casts = [
        'tanggal_aktivitas' => 'date',
        'waktu_log' => 'datetime',
    ];

    /**
     * Dapatkan peminjaman yang terkait dengan logbook ini (Relasi One-to-Many terbalik).
     */
    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }
}