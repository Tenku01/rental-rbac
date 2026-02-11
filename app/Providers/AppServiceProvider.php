<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Peminjaman;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. LOGIKA SUPERADMIN BYPASS (PENTING)
        // Membuat role 'admin' otomatis memiliki semua hak akses tanpa perlu dicek satu per satu.
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        View::composer('sopir.SopirDashboard', function ($view) {
    $user = Auth::user();

    if (! $user || ! method_exists($user, 'hasRole') || ! $user->hasRole('sopir')) {
        $view->with([
            'sopir' => null,
            'tugasAktif' => collect(),
            'riwayat' => collect(),
        ]);
        return;
    }

    $sopir = $user->sopir;

    if (! $sopir) {
        $view->with([
            'sopir' => null,
            'tugasAktif' => collect(),
            'riwayat' => collect(),
        ]);
        return;
    }

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
    
        // 3. OBSERVER
        User::observe(UserObserver::class);
    }
}