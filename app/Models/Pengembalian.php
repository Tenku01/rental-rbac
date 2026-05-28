<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengembalian extends Model
{
    use HasFactory;

    protected $table = 'pengembalian';
    protected $primaryKey = 'kode_pengembalian';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * 1. AKTIFKAN KEMBALI TIMESTAMPS
     * Karena pada migrasi terbaru kita telah menambahkan created_at dan updated_at
     */
    public $timestamps = true;

    protected $fillable = [
        'kode_pengembalian',
        'peminjaman_id',
        'tanggal_pengembalian',
        'status',
        
        // --- Data Inspeksi Mobil ---
        'pemeriksa_id',
        'kondisi_mobil',
        'catatan_inspeksi',
        
        // --- Data Denda ---
        'denda_keterlambatan',
        'denda_kerusakan',
        'total_denda',
        'status_denda',
        'metode_pembayaran_denda',
        'tanggal_pembayaran_denda',
        'keterangan_denda',
    ];

    protected $appends = ['total_outstanding_fine'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Logika generate kode unik: PBL0000001
            if (empty($model->kode_pengembalian)) {
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

    /** 🔹 Relasi ke user (Pemeriksa) */
    public function pemeriksa()
    {
        return $this->belongsTo(User::class, 'pemeriksa_id');
    }

    /** 🔹 Relasi ke laporan kerusakan */
    public function damageReports()
    {
        return $this->hasMany(LaporanKerusakanMobil::class, 'pengembalian_kode', 'kode_pengembalian');
    }

    /**
     * ACCESSOR: Menghitung total denda yang BELUM DIBAYAR.
     * Karena data denda sudah tergabung, kita cukup membaca kolomnya sendiri.
     */
    public function getTotalOutstandingFineAttribute()
    {
        if ($this->status_denda === 'belum dibayar') {
            return $this->total_denda;
        }
        
        return 0; // Jika tidak ada denda atau sudah dibayar
    }
    
    /**
     * Helper untuk mendapatkan total denda keseluruhan
     */
    public function getTotalDendaAttribute()
    {
        return $this->total_denda;
    }
}