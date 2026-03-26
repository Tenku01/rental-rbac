<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use App\Rules\RecaptchaRule; // Pastikan rule ini sudah dibuat di langkah sebelumnya

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Properti Form Object. 
     */
    public LoginForm $form;

    /**
     * Properti untuk menampung token reCAPTCHA dari frontend
     */
    public ?string $recaptcha = null;

    /**
     * Handle login
     */
    public function login(): void
    {
        // 1. Validasi reCAPTCHA terlebih dahulu
        $this->validate([
            'recaptcha' => ['required', new RecaptchaRule()]
        ], [
            'recaptcha.required' => 'Verifikasi keamanan gagal. Silakan centang "I\'m not a robot".'
        ]);

        // 2. Validasi form input bawaan (Email & Password)
        $this->validate();

        try {
            $this->form->authenticate();
        } catch (\Exception $e) {
            $this->addError('form.email', 'Email atau password salah.');
            
            // Kosongkan token dan minta frontend me-reset widget reCAPTCHA
            $this->recaptcha = null;
            $this->dispatch('reset-recaptcha');
            
            // 🔥 TRIGGER EFEK GETAR (SHAKE ANIMATION) 🔥
            $this->dispatch('login-failed');
            return;
        }

        Session::regenerate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // AMBIL ROLE PERTAMA
        $role = $user->getRoleNames()->first();

        // LOGIKA REDIRECT
        switch ($role) {
            case 'admin':
                $route = 'home';
                break;
            case 'staff':
                $route = 'staff.dashboard';
                break;
            case 'sopir':
                $route = 'sopir.dashboard';
                break;
            case 'pelanggan':
                $route = 'dashboard';
                break;
            default:
                $route = 'home';
                break;
        }

        $this->redirectIntended(route($route, absolute: false), navigate: true);
    }
};
?>

<div>
    <!-- Header Percantik UX -->
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-extrabold text-cyan-800 tracking-tight">Selamat Datang!</h2>
        <p class="text-sm text-gray-500 mt-2">Silakan masuk ke akun AKA Rentcar Anda</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- 🔥 BUNGKUSAN ALPINE UNTUK EFEK GETAR 🔥 -->
    <div x-data="{ shake: false }" 
         @login-failed.window="shake = true; setTimeout(() => shake = false, 500)">
        
        <!-- Form ditambahkan efek class binding :class="{ 'animate-shake': shake }" -->
        <form wire:submit="login" class="space-y-5" :class="{ 'animate-shake': shake }">
            
            <!-- Email -->
            <div>
                <x-input-label for="email" :value="__('Email')" class="text-gray-700 font-semibold" />
                <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full border-gray-300 focus:border-cyan-500 focus:ring-cyan-500 rounded-lg shadow-sm transition duration-200"
                    type="email" name="email" required autofocus autocomplete="username" placeholder="nama@email.com" />
                <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
            </div>

            <!-- Password dengan Show/Hide Feature -->
            <div x-data="{ showPassword: false }">
                <div class="flex items-center justify-between">
                    <x-input-label for="password" :value="__('Password')" class="text-gray-700 font-semibold" />
                    @if (Route::has('password.request'))
                        <a class="text-sm font-medium text-cyan-600 hover:text-cyan-800 transition duration-150 ease-in-out"
                           href="{{ route('password.request') }}" wire:navigate>
                            {{ __('Lupa password?') }}
                        </a>
                    @endif
                </div>
                
                <div class="relative mt-1">
                    <x-text-input wire:model="form.password" id="password" class="block w-full pr-10 border-gray-300 focus:border-cyan-500 focus:ring-cyan-500 rounded-lg shadow-sm transition duration-200"
                        x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••" />
                    
                    <!-- Ikon Mata (Toggle Password Visibility) -->
                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-cyan-600 focus:outline-none transition-colors">
                        <!-- Mata Terbuka (Muncul saat password tersembunyi) -->
                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Mata Dicoret (Muncul saat password terlihat) -->
                        <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div>
                <label for="remember" class="inline-flex items-center cursor-pointer group">
                    <input wire:model="form.remember" id="remember" type="checkbox"
                        class="rounded border-gray-400 text-cyan-600 shadow-sm focus:ring-cyan-500 cursor-pointer w-4 h-4 transition duration-150"
                        name="remember">
                    <span class="ms-2 text-sm text-gray-700 group-hover:text-cyan-800 transition-colors">{{ __('Ingat Saya') }}</span>
                </label>
            </div>

            <!-- Google reCAPTCHA v2 -->
  <div class="mt-4 w-full flex justify-center" wire:ignore>
    <div 
        class="g-recaptcha" 
        data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" 
        data-callback="setRecaptchaCallback">
    </div>
</div>

<x-input-error :messages="$errors->get('recaptcha')" class="mt-2" />

            <!-- Button Login dgn Efek Loading -->
            <div class="mt-6">
                <button type="submit" 
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-cyan-600 hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" 
                        wire:loading.attr="disabled">
                    
                    <span wire:loading.remove wire:target="login">{{ __('Log in') }}</span>
                    
                    <span wire:loading wire:target="login" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>
            </div>

            <!-- 🔥 SEPARATOR / PEMBATAS 🔥 -->
            <div class="mt-6 relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">ATAU MASUK DENGAN</span>
                </div>
            </div>

            <!-- 🔥 TOMBOL GOOGLE LOGIN 🔥 -->
            <div class="mt-6">
                <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-bold text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 transition-all">
                    <!-- Icon Google SVG -->
                    <svg class="h-5 w-5 mr-3" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Google
                </a>
            </div>

            <!-- Register Link -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600">
                    {{ __("Belum punya akun?") }}
                    <a href="{{ route('register') }}" class="font-bold text-cyan-600 hover:text-cyan-800 transition-colors" wire:navigate>
                        {{ __('Daftar sekarang') }}
                    </a>
                </p>
            </div>
        </form>
    </div>
    
    <!-- Script Pihak Ketiga & Custom JS -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        function setRecaptchaCallback(token) {
            @this.set('recaptcha', token);
        }

        document.addEventListener('reset-recaptcha', () => {
            if (typeof grecaptcha !== 'undefined') {
                grecaptcha.reset(); 
            }
        });
    </script>

    <!-- 🔥 CSS UNTUK ANIMASI GETAR (SHAKE) 🔥 -->
    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .animate-shake {
            animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
        }
    </style>
</div>