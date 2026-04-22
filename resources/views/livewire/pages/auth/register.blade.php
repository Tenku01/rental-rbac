<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Rules\RecaptchaRule;
use Livewire\WithFileUploads;

new #[Layout('layouts.guest')] class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $no_telepon = '';
    public string $alamat = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $recaptcha = null;
    
    // Properti untuk file upload (opsional)
    public $foto_ktp;
    public $foto_sim;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        // 1. Validasi reCAPTCHA
        $this->validate([
            'recaptcha' => ['required', new RecaptchaRule()]
        ], [
            'recaptcha.required' => 'Verifikasi keamanan gagal. Silakan centang "I\'m not a robot".'
        ]);

        // 2. Validasi Form
        try {
            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'no_telepon' => ['required', 'string', 'max:20'],
                'alamat' => ['required', 'string', 'max:500'],
                'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
                // Validasi gambar opsional, maksimal 5MB
                'foto_ktp' => ['nullable', 'image', 'max:5120'], 
                'foto_sim' => ['nullable', 'image', 'max:5120'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Reset captcha jika gagal validasi
            $this->recaptcha = null;
            $this->dispatch('reset-recaptcha');
            
            // 🔥 TRIGGER EFEK GETAR (SHAKE ANIMATION) 🔥
            $this->dispatch('register-failed');
            throw $e;
        }

        // 3. Handle Upload File Jika Ada
        $ktpPath = null;
        $simPath = null;
        
        if ($this->foto_ktp) {
            $ktpPath = $this->foto_ktp->store('ktp', 'public');
        }
        
        if ($this->foto_sim) {
            $simPath = $this->foto_sim->store('sim', 'public');
        }

        // Tentukan status verifikasi (Jika ada dokumen yang diunggah, masuk antrean 'menunggu')
        $statusVerifikasi = ($ktpPath || $simPath) ? 'menunggu' : 'belum_upload';

        // 4. Create User
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'no_telepon' => $validated['no_telepon'],
            'alamat' => $validated['alamat'],
            'password' => Hash::make($validated['password']),
            'status' => 'aktif',
            'foto_ktp' => $ktpPath,
            'foto_sim' => $simPath,
            'status_verifikasi' => $statusVerifikasi,
        ]);

        // Assign Role Spatie
        if (method_exists($user, 'assignRole')) {
            $user->assignRole('pelanggan');
        }

        // Trigger Observer
        $user->touch();

        // Event Registered (Kirim email verifikasi)
        event(new Registered($user));

        // Berikan feedback sukses
        session()->flash('status', 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi sebelum login.');
        
        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div>
    <!-- Header -->
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-extrabold text-cyan-800 tracking-tight">Daftar Akun Baru</h2>
        <p class="text-sm text-gray-600 mt-2">Mulai perjalanan Anda bersama AKA Rentcar</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div x-data="{ shake: false }" 
         @register-failed.window="shake = true; setTimeout(() => shake = false, 500)">
        
        <form wire:submit="register" class="space-y-5" :class="{ 'animate-shake': shake }">
            
            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Nama Lengkap')" />
                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" placeholder="Nama lengkap sesuai identitas" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" placeholder="email@contoh.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- ========================================== -->
            <!-- INFORMASI KONTAK & OPERASIONAL (BARU)      -->
            <!-- ========================================== -->
            <div class="pt-2">
                <div class="flex items-start p-3 mb-4 text-xs text-cyan-800 border border-cyan-200 rounded-lg bg-cyan-50" role="alert">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-2 mt-[1px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <span class="font-bold">Informasi:</span> Data Nomor HP dan Alamat diperlukan sebagai sarana komunikasi operasional dan validasi saat Anda menyewa mobil.
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- No Telepon -->
                    <div>
                        <x-input-label for="no_telepon" :value="__('Nomor WhatsApp / HP')" />
                        <x-text-input wire:model="no_telepon" id="no_telepon" class="block mt-1 w-full" type="text" name="no_telepon" required placeholder="08xxxxxxxxxx" />
                        <x-input-error :messages="$errors->get('no_telepon')" class="mt-2" />
                    </div>

                    <!-- Alamat -->
                    <div>
                        <x-input-label for="alamat" :value="__('Alamat Domisili')" />
                        <x-text-input wire:model="alamat" id="alamat" class="block mt-1 w-full" type="text" name="alamat" required placeholder="Jalan, RT/RW, Kota" />
                        <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                    </div>
                </div>
            </div>
            <!-- ========================================== -->

            <!-- Password dengan Show/Hide Feature -->
            <div x-data="{ showPassword: false }" class="pt-2">
                <x-input-label for="password" :value="__('Password')" />
                <div class="relative mt-1">
                    <x-text-input wire:model="password" id="password" class="block w-full pr-10"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        name="password" required autocomplete="new-password" placeholder="••••••••" />
                    
                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-cyan-600 focus:outline-none">
                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div x-data="{ showConfirm: false }">
                <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                <div class="relative mt-1">
                    <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block w-full pr-10"
                        x-bind:type="showConfirm ? 'text' : 'password'"
                        name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
                    
                    <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-cyan-600 focus:outline-none">
                        <svg x-show="!showConfirm" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showConfirm" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- ========================================== -->
            <!-- DOKUMEN IDENTITAS (KTP & SIM) - OPSIONAL   -->
            <!-- ========================================== -->
            <div class="pt-5 border-t border-gray-200 mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Dokumen Identitas (Opsional)</h3>
                
                <!-- Banner Pemberitahuan -->
                <div class="flex items-start p-4 mb-4 text-sm text-amber-800 border border-amber-300 rounded-lg bg-amber-50" role="alert">
                    <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                    </svg>
                    <div>
                        <span class="font-medium">Penting untuk menyewa mobil:</span> Anda dapat mengosongkan bagian ini sekarang. Namun, KTP dan SIM wajib diunggah nantinya agar Anda dapat melakukan penyewaan.
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Upload KTP -->
                    <div>
                        <x-input-label for="foto_ktp" :value="__('Foto KTP')" />
                        <input wire:model="foto_ktp" id="foto_ktp" type="file" accept="image/*" class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-cyan-50 file:text-cyan-700
                            hover:file:bg-cyan-100 mt-1 cursor-pointer
                        " />
                        <x-input-error :messages="$errors->get('foto_ktp')" class="mt-2" />
                        
                        <!-- Preview KTP -->
                        <div wire:loading wire:target="foto_ktp" class="text-sm text-cyan-600 mt-2">Mengunggah...</div>
                        @if ($foto_ktp)
                            <div class="mt-2 relative inline-block">
                                <img src="{{ $foto_ktp->temporaryUrl() }}" class="h-24 w-auto object-cover rounded shadow-sm border border-gray-200">
                            </div>
                        @endif
                    </div>

                    <!-- Upload SIM -->
                    <div>
                        <x-input-label for="foto_sim" :value="__('Foto SIM')" />
                        <input wire:model="foto_sim" id="foto_sim" type="file" accept="image/*" class="block w-full text-sm text-gray-500
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-cyan-50 file:text-cyan-700
                            hover:file:bg-cyan-100 mt-1 cursor-pointer
                        " />
                        <x-input-error :messages="$errors->get('foto_sim')" class="mt-2" />
                        
                        <!-- Preview SIM -->
                        <div wire:loading wire:target="foto_sim" class="text-sm text-cyan-600 mt-2">Mengunggah...</div>
                        @if ($foto_sim)
                            <div class="mt-2 relative inline-block">
                                <img src="{{ $foto_sim->temporaryUrl() }}" class="h-24 w-auto object-cover rounded shadow-sm border border-gray-200">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- ========================================== -->

            <!-- Google reCAPTCHA v2 (Centered) -->
            <div class="mt-4 w-full flex justify-center" wire:ignore>
                <div 
                    class="g-recaptcha" 
                    data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" 
                    data-callback="setRecaptchaCallback">
                </div>
            </div>
            <x-input-error :messages="$errors->get('recaptcha')" class="mt-2 text-center" />

            <!-- Submit Button dgn Efek Loading -->
            <div class="mt-6">
                <x-primary-button class="w-full justify-center py-3" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="register, foto_ktp, foto_sim">{{ __('Daftar Sekarang') }}</span>
                    <span wire:loading wire:target="register, foto_ktp, foto_sim" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </x-primary-button>
            </div>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Sudah punya akun?') }}
                    <a href="{{ route('login') }}" class="font-bold text-cyan-600 hover:text-cyan-800 transition" wire:navigate>
                        {{ __('Login di sini') }}
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