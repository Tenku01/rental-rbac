<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanKerusakanMobil extends Model
{
    use HasFactory;
    
    protected $table = 'laporan_kerusakan_mobil';
    protected $primaryKey = 'kode_laporan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'mobil_id',
        'pengembalian_kode',
        'deskripsi_kerusakan', // 🔹 Diperbarui dari damage_description
        'biaya_kerusakan'      // 🔹 Diperbarui dari damage_cost
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->kode_laporan)) {
                // 🔹 Membuat kode unik DMG + Plat Mobil + Waktu agar tidak bentrok
                // Menghindari error karena peminjaman_id tidak ada di tabel ini
                $mobilIdBersih = str_replace(' ', '', strtoupper($model->mobil_id));
                $model->kode_laporan = 'DMG-' . $mobilIdBersih . '-' . time();
            }
        });
    }

    /** 🔹 Relasi ke mobil */
    public function mobil()
    {
        return $this->belongsTo(Mobil::class, 'mobil_id');
    }

    /** 🔹 Relasi ke pengembalian */
    public function pengembalian()
    {
        return $this->belongsTo(Pengembalian::class, 'pengembalian_kode', 'kode_pengembalian');
    }
}