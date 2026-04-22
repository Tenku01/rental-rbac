<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pengembalian extends Model
{
    use HasFactory;

    protected $table = 'pengembalian';
    protected $primaryKey = 'kode_pengembalian';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * 1. NONAKTIFKAN TIMESTAMPS
     * Karena tabel database Anda tidak memiliki kolom created_at & updated_at
     */
    public $timestamps = false;

    protected $fillable = [
        'kode_pengembalian',
        'peminjaman_id',
        'tanggal_pengembalian',
        'status',
    ];

    protected $appends = ['total_outstanding_fine'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Logika generate kode unik: PBL0000001
            if (empty($model->kode_pengembalian)) {
                /**
                 * 2. PERBAIKI ORDER BY
                 * Jangan gunakan 'created_at' karena kolomnya tidak ada.
                 * Gunakan 'tanggal_pengembalian' atau primary key.
                 */
                $latest = static::orderBy('tanggal_pengembalian', 'desc')->first();
                
                $number = $latest ? (int) substr($latest->kode_pengembalian, 3) + 1 : 1;
                $model->kode_pengembalian = 'PBL' . str_pad($number, 7, '0', STR_PAD_LEFT);
            }
            
            if (empty($model->status)) {
                $model->status = 'menunggu pengecekan';
            }
        });
    }

    /** 🔹 Relasi ke peminjaman */
    public function peminjaman()
    {
        return $this->belongsTo(Peminjaman::class, 'peminjaman_id');
    }

    /** 🔹 Relasi ke denda (fines) */
    public function fines()
    {
        return $this->hasMany(Denda::class, 'peminjaman_id', 'peminjaman_id');
    }

    /** 🔹 Relasi ke laporan kerusakan */
    public function damageReports()
    {
        return $this->hasMany(LaporanKerusakanMobil::class, 'pengembalian_kode', 'kode_pengembalian');
    }

    /** 🔹 Relasi ke inspeksi kendaraan */
    public function inspections()
    {
        return $this->hasMany(InspeksiMobil::class, 'pengembalian_kode', 'kode_pengembalian');
    }

    /**
     * ACCESSOR: Menghitung total denda yang BELUM DIBAYAR.
     */
    public function getTotalOutstandingFineAttribute()
    {
        return $this->fines()
                    ->where('status', 'belum dibayar') 
                    ->sum('total_denda');
    }
    
    /**
     * Helper untuk mendapatkan total denda keseluruhan
     */
    public function getTotalDendaAttribute()
    {
        return $this->fines()->sum('total_denda');
    }
}