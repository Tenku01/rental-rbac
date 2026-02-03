<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <!-- Icon Container -->
            <div class="p-2.5 bg-cyan-100 rounded-2xl shadow-sm border border-cyan-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <h2 class="font-extrabold text-2xl text-slate-800 leading-tight tracking-tight">
                {{ __('Pengaturan Profil') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="grid grid-cols-1 gap-8">
                
                <!-- Informasi Akun -->
                <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="flex flex-col md:flex-row">
                        <!-- Sidebar Card -->
                        <div class="p-10 md:w-1/3 bg-cyan-50/40 border-b md:border-b-0 md:border-r border-cyan-100/50">
                            <div class="flex items-center gap-4 mb-5">
                                <span class="p-2.5 bg-cyan-500 rounded-2xl shadow-lg shadow-cyan-200/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <h3 class="text-xl font-bold text-slate-800">{{ __('Informasi Akun') }}</h3>
                            </div>
                            <p class="text-[0.95rem] text-slate-500 leading-relaxed">
                                Perbarui identitas publik Anda dan alamat email untuk menjaga keamanan komunikasi akun Anda.
                            </p>
                        </div>
                        <!-- Content Card -->
                        <div class="p-10 md:w-2/3">
                            <livewire:profile.update-profile-information-form />
                        </div>
                    </div>
                </section>

                <!-- Keamanan / Password -->
                <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="flex flex-col md:flex-row">
                        <div class="p-10 md:w-1/3 bg-cyan-50/40 border-b md:border-b-0 md:border-r border-cyan-100/50">
                            <div class="flex items-center gap-4 mb-5">
                                <span class="p-2.5 bg-cyan-500 rounded-2xl shadow-lg shadow-cyan-200/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <h3 class="text-xl font-bold text-slate-800">{{ __('Kata Sandi') }}</h3>
                            </div>
                            <p class="text-[0.95rem] text-slate-500 leading-relaxed">
                                Gunakan kombinasi karakter yang unik untuk memastikan akun Anda tetap aman dari akses tidak sah.
                            </p>
                        </div>
                        <div class="p-10 md:w-2/3">
                            <livewire:profile.update-password-form />
                        </div>
                    </div>
                </section>

                <!-- Hapus Akun -->
                <section class="bg-white rounded-[2.5rem] shadow-sm border border-red-100 overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="flex flex-col md:flex-row">
                        <div class="p-10 md:w-1/3 bg-red-50/30 border-b md:border-b-0 md:border-r border-red-100/50">
                            <div class="flex items-center gap-4 mb-5">
                                <span class="p-2.5 bg-red-500 rounded-2xl shadow-lg shadow-red-200/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <h3 class="text-xl font-bold text-red-700">{{ __('Hapus Akun') }}</h3>
                            </div>
                            <p class="text-[0.95rem] text-red-600/70 leading-relaxed">
                                Seluruh data, aset, dan riwayat akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
                            </p>
                        </div>
                        <div class="p-10 md:w-2/3">
                            <livewire:profile.delete-user-form />
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
</x-app-layout>