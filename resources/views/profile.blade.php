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

    <div class="py-12 bg-slate-50 min-h-screen font-inter">
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
            .font-inter { font-family: 'Inter', sans-serif; }
        </style>
        
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
                                Perbarui identitas publik Anda, nomor telepon, alamat domisili, serta alamat email untuk memastikan komunikasi tetap lancar.
                            </p>
                        </div>
                        <!-- Content Card -->
                        <div class="p-10 md:w-2/3">
                            <livewire:profile.update-profile-information-form />
                        </div>
                    </div>
                </section>

                <!-- Status Verifikasi & Identitas -->
                <section class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden transition-all duration-300 hover:shadow-md">
                    <div class="flex flex-col md:flex-row">
                        <div class="p-10 md:w-1/3 bg-emerald-50/40 border-b md:border-b-0 md:border-r border-emerald-100/50">
                            <div class="flex items-center gap-4 mb-5">
                                <span class="p-2.5 bg-emerald-500 rounded-2xl shadow-lg shadow-emerald-200/50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <h3 class="text-xl font-bold text-slate-800">{{ __('Verifikasi Identitas') }}</h3>
                            </div>
                            <p class="text-[0.95rem] text-slate-500 leading-relaxed">
                                Kelola dokumen KTP dan SIM Anda. Dokumen ini wajib diunggah dan disetujui sebelum Anda dapat menyewa kendaraan.
                            </p>
                        </div>
                        <div class="p-10 md:w-2/3 flex flex-col justify-center">
                            @php
                                $user = Auth::user();
                                $status = $user->status_verifikasi;
                                
                                $badgeClass = match($status) {
                                    'disetujui' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    'menunggu' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                    'ditolak' => 'bg-red-100 text-red-700 border-red-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                                
                                $statusLabel = match($status) {
                                    'disetujui' => 'Telah Diverifikasi',
                                    'menunggu' => 'Menunggu Pengecekan',
                                    'ditolak' => 'Ditolak',
                                    default => 'Belum Upload'
                                };
                            @endphp

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div>
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Status Saat Ini</h4>
                                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-[11px] font-black uppercase tracking-widest border {{ $badgeClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                
                                <div>
                                    @if($status === 'belum_upload')
                                        <a href="{{ route('upload.identity') }}" class="inline-flex justify-center items-center px-5 py-2.5 bg-emerald-600 text-white text-sm font-bold rounded-xl shadow-sm hover:bg-emerald-700 transition">
                                            Upload Dokumen
                                        </a>
                                    @elseif(in_array($status, ['ditolak', 'disetujui', 'menunggu']))
                                        <a href="{{ route('edit.identity', $user->id) }}" class="inline-flex justify-center items-center px-5 py-2.5 bg-white border border-slate-300 text-slate-700 text-sm font-bold rounded-xl shadow-sm hover:bg-slate-50 transition">
                                            Lihat / Perbarui Dokumen
                                        </a>
                                    @endif
                                </div>
                            </div>

                            @if($status === 'ditolak' && $user->alasan_penolakan)
                                <div class="bg-red-50 border border-red-100 rounded-2xl p-5 mt-6">
                                    <h5 class="text-[10px] font-black text-red-800 uppercase tracking-widest mb-1.5 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Alasan Penolakan:
                                    </h5>
                                    <p class="text-sm font-medium text-red-600">{{ $user->alasan_penolakan }}</p>
                                </div>
                            @endif
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