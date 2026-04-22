<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspeksiMobil extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'inspeksi_mobil';

    /**
     * Atribut yang dapat diisi (Mass Assignable).
     * Saya sesuaikan staff_id menjadi pemeriksa_id agar sinkron dengan
     * logic di Livewire Component yang kita buat sebelumnya.
     */
    protected $fillable = [
        'mobil_id',
        'pemeriksa_id', // Menggunakan pemeriksa_id agar lebih universal (bisa staff/admin)
        'pengembalian_kode',
        'kondisi',
        'keterangan'
    ];

    /**
     * Relasi ke tabel Mobil
     * mobil_id di sini berisi plat nomor yang merupakan Primary Key di tabel mobils
     */
    public function mobil(): BelongsTo
    {
        return $this->belongsTo(Mobil::class, 'mobil_id', 'id');
    }

    /**
     * Relasi ke tabel User (Siapa yang melakukan audit/inspeksi)
     */
    public function pemeriksa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemeriksa_id');
    }

    /**
     * Relasi ke tabel Pengembalian (Opsional tapi sangat berguna)
     * Digunakan untuk melihat histori pengembalian mana yang memicu inspeksi ini.
     */
    public function pengembalian(): BelongsTo
    {
        return $this->belongsTo(Pengembalian::class, 'pengembalian_kode', 'kode_pengembalian');
    }
}