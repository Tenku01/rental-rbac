<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
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
    | Relasi ke Tabel Profil
    |--------------------------------------------------------------------------
    */

    public function staff() { return $this->hasOne(Staff::class, 'user_id'); }
    public function sopir() { return $this->hasOne(Sopir::class, 'user_id'); }
    public function pelanggan() { return $this->hasOne(Pelanggan::class, 'user_id'); }
    public function resepsionis() { return $this->hasOne(Resepsionis::class, 'user_id'); }
    public function peminjamans() { return $this->hasMany(Peminjaman::class); }

    /*
    |--------------------------------------------------------------------------
    | Booted Logic (Otomatisasi Pembuatan Profil)
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        /**
         * Triggered saat User::create() atau $user->save() pertama kali.
         * Penting: Pastikan assignRole() dipanggil SEGERA setelah User::create() 
         * di Controller agar logika ini berjalan.
         */
        static::created(function ($user) {
            // Jika user mendaftar sendiri via Breeze (tanpa role), default jadi pelanggan
            if ($user->roles()->count() === 0) {
                $user->assignRole('pelanggan');
            }
        });

        /**
         * Triggered saat role diberikan kepada user (via assignRole).
         * Ini akan otomatis membuat data di tabel detail (pelanggan/sopir/staff/resepsionis).
         */
        static::saved(function ($user) {
            // Logika pembuatan profil detail berdasarkan Role Spatie
            if ($user->hasRole('pelanggan')) {
                Pelanggan::firstOrCreate(['user_id' => $user->id], [
                    'nama' => $user->name,
                ]);
            } 
            
            if ($user->hasRole('resepsionis')) {
                Resepsionis::firstOrCreate(['user_id' => $user->id], [
                    'nama' => $user->name,
                    'status' => 'tidak aktif'
                ]);
            }

            if ($user->hasRole('sopir')) {
                Sopir::firstOrCreate(['user_id' => $user->id], [
                    'nama' => $user->name,
                    'status' => 'tidak tersedia'
                ]);
            }

            if ($user->hasRole('staff')) {
                Staff::firstOrCreate(['user_id' => $user->id], [
                    'nama' => $user->name,
                    'status' => 'tidak aktif'
                ]);
            }
        });

        // Hapus detail saat user dihapus
        static::deleted(function ($user) {
            $user->pelanggan()?->delete();
            $user->resepsionis()?->delete();
            $user->sopir()?->delete();
            $user->staff()?->delete();
        });
    }
    
}