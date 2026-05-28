<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mobil extends Model
{
    use HasFactory;

    protected $table = 'mobils';

    // ----------------------------------------------------------------------
    // KONFIGURASI PENTING UNTUK ID MANUAL (PLAT NOMOR)
    // ----------------------------------------------------------------------
    
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id', 
        'tipe',
        'status',
        'merek',
        'warna',
        'transmisi',
        'kursi',
        'harga',
        'foto',
        // --- Kolom Baru ---
        'status_kepemilikan',
        'nama_pemilik',
        'persentase_bagi_hasil_rental',
        'persentase_bagi_hasil_mitra',
    ];

    // ----------------------------------------------------------------------
    // RELASI
    // ----------------------------------------------------------------------
    
    // Relasi ke Peminjaman (Digunakan di Admin Livewire untuk cek hapus)
    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class, 'mobil_id');
    }

    // Alias untuk peminjaman (jika ada kode lama yang pakai nama ini)
    public function peminjamans()
    {
        return $this->hasMany(Peminjaman::class, 'mobil_id');
    }

    // Relasi ke Laporan Kerusakan Mobil
    public function damageReports()
    {
        return $this->hasMany(LaporanKerusakanMobil::class, 'mobil_id');
    }

    // Catatan: Relasi user() dan inspections() telah dihapus sesuai dengan
    // revisi penyederhanaan database dan pengubahan kepemilikan mitra.
}