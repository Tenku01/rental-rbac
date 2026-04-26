<?php

namespace App\Models;

use App\Models\InspeksiMobil;
use App\Models\Pesan;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'status',
        'alamat', 'no_telepon', 'foto_ktp', 'foto_sim', 
        'status_verifikasi', 'alasan_penolakan'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    protected $guard_name = 'web';

    /*
    |--------------------------------------------------------------------------
    | Relasi Database Baru
    |--------------------------------------------------------------------------
    */

    // Tabel Sopir masih kita pertahankan, jadi relasi ini tetap ada
    public function sopir() { 
        return $this->hasOne(Sopir::class, 'user_id'); 
    }

    // Nama fungsi peminjamans diubah jadi peminjaman (singular function name for consistency)
    public function peminjaman() { 
        return $this->hasMany(Peminjaman::class, 'user_id'); 
    }

    // Relasi ke tabel pesan (User sebagai pengirim)
    public function pesan() {
        return $this->hasMany(Pesan::class, 'pengirim_id');
    }

    // Relasi ke tabel inspeksi mobil (User sebagai pemeriksa/staff)
    public function inspeksi_mobil() {
        return $this->hasMany(InspeksiMobil::class, 'pemeriksa_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Booted Logic (Otomatisasi)
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        /**
         * Triggered saat User::create() pertama kali (misal via halaman Register).
         */
        static::created(function ($user) {
            // Jika user mendaftar sendiri via Breeze (tanpa role), default jadi pelanggan
            if ($user->roles()->count() === 0) {
                $user->assignRole('pelanggan');
            }
        });

        /**
         * Triggered saat data disave/diupdate.
         * Hanya menyisakan logika untuk Sopir karena tabel lain sudah dihapus.
         */
        static::saved(function ($user) {
            if ($user->hasRole('sopir')) {
                Sopir::firstOrCreate(['user_id' => $user->id], [
                    'nama' => $user->name,
                    'status' => 'tidak tersedia'
                ]);
            }
            
            // Logika Pelanggan, Resepsionis, dan Staff DIHAPUS 
            // karena datanya sekarang numpang langsung di tabel users.
        });

        /**
         * Hapus relasi yang tersisa saat user dihapus
         */
        static::deleted(function ($user) {
            $user->sopir()?->delete();
            // Penghapusan relasi lain juga dihilangkan.
        });
    }

    public function isOnline()
{
    return Cache::has('user-is-online-' . $this->id);
}
}