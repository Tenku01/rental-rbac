<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    // State untuk mengatur visibilitas password
    public bool $show_current = false;
    public bool $show_new = false;
    public bool $show_confirm = false;

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-bold text-cyan-800">
            {{ __('Ubah Kata Sandi') }}
        </h2>

        <p class="mt-1 text-sm text-cyan-500">
            {{ __('Pastikan akun Anda menggunakan kata sandi yang panjang dan aman.') }}
        </p>
    </header>

    <form wire:submit="updatePassword" class="mt-8 space-y-6">
        <!-- Password Saat Ini -->
        <div>
            <x-input-label for="update_password_current_password" :value="__('Kata Sandi Saat Ini')" class="text-cyan-700 font-semibold mb-1"/>
            <div class="relative mt-1">
                <x-text-input 
                    wire:model="current_password" 
                    id="update_password_current_password" 
                    name="current_password" 
                    :type="$show_current ? 'text' : 'password'" 
                    class="block w-full bg-white border-cyan-200 focus:border-cyan-500 focus:ring-cyan-500 rounded-xl shadow-sm transition-all duration-200 pr-12" 
                    autocomplete="current-password" 
                />
                <button type="button" wire:click="$toggle('show_current')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-cyan-400 hover:text-cyan-600 transition-colors">
                    @if($show_current)
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    @endif
                </button>
            </div>
            <x-input-error :messages="$errors->get('current_password')" class="mt-2 text-red-600 font-medium text-xs" />
        </div>

        <!-- Password Baru -->
        <div>
            <x-input-label for="update_password_password" :value="__('Kata Sandi Baru')" class="text-cyan-700 font-semibold mb-1"/>
            <div class="relative mt-1">
                <x-text-input 
                    wire:model="password" 
                    id="update_password_password" 
                    name="password" 
                    :type="$show_new ? 'text' : 'password'" 
                    class="block w-full bg-white border-cyan-200 focus:border-cyan-500 focus:ring-cyan-500 rounded-xl shadow-sm transition-all duration-200 pr-12" 
                    autocomplete="new-password" 
                />
                <button type="button" wire:click="$toggle('show_new')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-cyan-400 hover:text-cyan-600 transition-colors">
                    @if($show_new)
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    @endif
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600 font-medium text-xs" />
        </div>

        <!-- Konfirmasi Password -->
        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Kata Sandi')" class="text-cyan-700 font-semibold mb-1"/>
            <div class="relative mt-1">
                <x-text-input 
                    wire:model="password_confirmation" 
                    id="update_password_password_confirmation" 
                    name="password_confirmation" 
                    :type="$show_confirm ? 'text' : 'password'" 
                    class="block w-full bg-white border-cyan-200 focus:border-cyan-500 focus:ring-cyan-500 rounded-xl shadow-sm transition-all duration-200 pr-12" 
                    autocomplete="new-password" 
                />
                <button type="button" wire:click="$toggle('show_confirm')" class="absolute inset-y-0 right-0 flex items-center pr-4 text-cyan-400 hover:text-cyan-600 transition-colors">
                    @if($show_confirm)
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    @endif
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-600 font-medium text-xs" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button >
                {{ __('Simpan Perubahan') }}
            </x-primary-button>

            <x-action-message class="text-emerald-600 font-semibold italic text-sm" on="password-updated">
                {{ __('Berhasil disimpan.') }}
            </x-action-message>
        </div>
    </form>
</section>