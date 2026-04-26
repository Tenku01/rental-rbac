<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Peminjaman;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // =========================================================================
        // 1. PELACAK STATUS ONLINE/OFFLINE (VIA CACHE)
        // =========================================================================
        
        // Deteksi Saat Login: Langsung buat cache aktif
        Event::listen(Login::class, function ($event) {
            if ($event->user) {
                Cache::put('user-is-online-' . $event->user->id, true, now()->addMinutes(5));
            }
        });

        // Deteksi Saat Logout: Langsung hapus cache agar jadi Nonaktif seketika
        Event::listen(Logout::class, function ($event) {
            if ($event->user) {
                Cache::forget('user-is-online-' . $event->user->id);
            }
        });

        // =========================================================================
        // 2. VIEW COMPOSER UNTUK SOPIR
        // =========================================================================
        View::composer('sopir.SopirDashboard', function ($view) {
            $user = Auth::user();
            if (! $user || ! $user->sopir) {
                $view->with([
                    'sopir' => null,
                    'tugasAktif' => collect(),
                    'riwayat' => collect(),
                ]);
                return;
            }

            $sopir = $user->sopir;
            $view->with([
                'sopir' => $sopir,
                'tugasAktif' => Peminjaman::where('sopir_id', $sopir->id)
                    ->whereIn('status', ['berlangsung', 'sudah dibayar lunas', 'pembayaran dp'])
                    ->with(['user', 'mobil'])
                    ->orderBy('tanggal_sewa')
                    ->get(),

                'riwayat' => Peminjaman::where('sopir_id', $sopir->id)
                    ->whereIn('status', ['selesai', 'dibatalkan'])
                    ->with(['user', 'mobil'])
                    ->orderByDesc('tanggal_kembali')
                    ->limit(10)
                    ->get(),
            ]);
        });
        
        // =========================================================================
        // 3. OBSERVER
        // =========================================================================
        User::observe(UserObserver::class);
    }
}