<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectByRole($request);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return $this->redirectByRole($request);
    }

    /**
     * 🔹 Tentukan redirect berdasarkan role user
     */
   private function redirectByRole(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Mapping Role ID ke Route Name Dashboard
        // Role ID: 1=Admin, 2=Pelanggan, 3=Resepsionis, 4=Sopir, 5=Staff
         $routeDashboard = match ($user->role_id) {
        1 => 'home',             // admin
        5 => 'staff.dashboard',  // staff
        4 => 'sopir.dashboard',  // sopir
        2 => 'dashboard',        // pelanggan
        3 => 'resepsionis.dashboard', // resepsionis (tambahan jika ada)
        default => 'home',       // fallback
    };

        return redirect()->intended(route($routeDashboard, absolute: false) . '?verified=1');
    }
}
