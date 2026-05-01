<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    protected $fillable = [
        'user_id',
        'mobil_id',
        'sopir_id', // Pastikan kolom ini ada di fillable
        'tanggal_sewa',
        'jam_sewa',
        'tanggal_kembali',
        'tambahan_sopir',
        'total_harga',
        'dp_dibayarkan',
        'sisa_bayar',
        'total_dibayarkan',
        'status',
        'metode_pembayaran',
        'bukti_transaksi',
        'kondisi_mobil',
        'tipe_pembayaran',
        'sudah_refund',
        'denda' 
    ];

    public function logbooks()
{
    return $this->hasMany(LogbookSopir::class, 'peminjaman_id');
}

    // Relasi ke User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Mobil
    public function mobil(): BelongsTo
    {
        return $this->belongsTo(Mobil::class);
    }
    

    // Relasi ke Sopir
    public function sopir(): BelongsTo
    {
        return $this->belongsTo(Sopir::class, 'sopir_id');
    }

    // Relasi Pembatalan (One to One)
    public function pembatalan(): HasOne
    {
        return $this->hasOne(PembatalanPesanan::class, 'peminjaman_id');
    }

    // Relasi ke Transaksi Pembayaran (Midtrans)
    public function TransaksiPembayaran(): HasMany
    {
        return $this->hasMany(TransaksiPembayaran::class, 'peminjaman_id');
    }


    public function denda(): HasMany
    {
        return $this->hasMany(Denda::class, 'peminjaman_id');
    }

    // Relasi ke Pengembalian
    public function pengembalian(): HasOne
    {
        return $this->hasOne(Pengembalian::class, 'peminjaman_id');
    }

    // Scopes & Helpers
    public function scopeUrutkanStatus($query)
    {
        return $query->orderByRaw("FIELD(status, 'dibatalkan', 'menunggu pembayaran','sudah dibayar lunas', 'pembayaran dp', 'berlangsung', 'selesai')");
    }

    public function getDp50Attribute()
    {
        return $this->total_harga * 0.5;
    }

    public function getSisaBayarAttribute()
    {
        return $this->total_harga - $this->dp_dibayarkan;
    }
}