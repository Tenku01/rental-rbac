<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Properti Form Object. 
     * Livewire 3 akan menginisialisasi ini secara otomatis.
     */
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        try {
            $this->form->authenticate();
        } catch (\Exception $e) {
            $this->addError('form.email', 'Email atau password salah.');
            return;
        }

        Session::regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // LOGIKA REDIRECT UNIVERSAL
        if ($user->hasRole('admin')) {
        $this->redirectIntended(default: route('home', absolute: false), navigate: true);
    } elseif ($user->hasRole('staff')) {
        $this->redirectIntended(default: route('staff.dashboard', absolute: false), navigate: true);
    } elseif ($user->hasRole('sopir')) {
        $this->redirectIntended(default: route('sopir.dashboard', absolute: false), navigate: true);
    } else {
        // Default untuk Pelanggan
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
        
    }
};
?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="form.password" id="password" class="block mt-1 w-full"
                          type="password"
                          name="password"
                          required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" class="rounded border-gray-500 text-cyan-600 shadow-sm focus:ring-cyan-500" name="remember">
                <span class="ms-2 text-sm text-gray-900">{{ __('Ingat Saya') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <!-- Tombol Login -->
        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        <!-- Link Register -->
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">
                {{ __("Belum punya akun?") }}
                <a href="{{ route('register') }}" class="text-cyan-500 hover:text-cyan-700">
                    {{ __('Register') }}
                </a>
            </p>
        </div>
    </form>
</div>