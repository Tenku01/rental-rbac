<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
    /**
     * Mengarahkan user ke halaman login Google
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani balasan (callback) dari Google setelah user login
     */
    public function callback()
    {
        try {
            // Ambil data user dari Google
            $googleUser = Socialite::driver('google')->user();

            // Cek apakah user dengan email ini sudah ada di database kita
            $user = User::where('email', $googleUser->getEmail())->first();

            // Jika belum ada, buat akun baru secara otomatis!
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(Str::random(16)), // Buat password acak yang kuat
                    'email_verified_at' => now(), // Anggap email Google sudah terverifikasi otomatis
                ]);

                // Wajib! Beri role default 'pelanggan'
                $user->assignRole('pelanggan'); 
            }

            // Lakukan proses login (paksa masuk)
            Auth::login($user);

            // Redirect sesuai role
            $role = $user->getRoleNames()->first();
            
            switch ($role) {
                case 'admin':
                    return redirect()->route('home');
                case 'staff':
                    return redirect()->route('staff.dashboard');
                case 'sopir':
                    return redirect()->route('sopir.dashboard');
                default:
                    return redirect()->route('dashboard'); // Pelanggan ke dashboard
            }

        } catch (\Exception $e) {
            // 🔥 KITA UBAH BAGIAN INI UNTUK MELIHAT ERROR ASLINYA 🔥
            dd('Terjadi Error: ' . $e->getMessage());
        }
    }
}