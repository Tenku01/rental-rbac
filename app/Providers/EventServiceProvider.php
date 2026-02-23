<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Pemetaan event dan listener untuk aplikasi.
     */
    protected $listen = [
        // Daftarkan event Registered agar Laravel mengirim email verifikasi
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Bootstrapping provider.
     */
    public function boot(): void
    {
        // Daftarkan observer untuk model User
        User::observe(UserObserver::class);
    }

    /**
     * Menentukan apakah event discovery diaktifkan secara otomatis.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}