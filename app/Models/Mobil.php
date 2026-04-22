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
    ];

    // ----------------------------------------------------------------------
    // RELASI
    // ----------------------------------------------------------------------

    // Relasi ke User (Jika mobil dimiliki user tertentu/mitra)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
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

    public function damageReports()
    {
        return $this->hasMany(LaporanKerusakanMobil::class, 'mobil_id');
    }

    public function inspections()
    {
        return $this->hasMany(InspeksiMobil::class, 'mobil_id');
    }
}