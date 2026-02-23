<div class="p-8 space-y-10 bg-[#f9fafb] min-h-screen font-inter">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
    </style>

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-gray-200 pb-8">
        <div class="flex items-center gap-5">
            <div class="h-16 w-16 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-cyan-100 ring-4 ring-cyan-50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Halo, {{ Auth::user()->name }}!</h1>
                <p class="text-sm text-gray-500 font-medium mt-2">Selamat datang di pusat kendali operasional Driver.</p>
            </div>
        </div>

        <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 ml-3">Ketersediaan:</span>
            <button wire:click="toggleStatus" 
                class="px-5 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all
                {{ $sopir && $sopir->status === 'Tidak Tersedia' ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }}">
                {{ $sopir ? $sopir->status : '...' }}
            </button>
        </div>
    </div>

    @if ($sopir)
        @if ($sopir->status === 'Tidak Tersedia')
            {{-- Tampilan Non-Aktif --}}
            <div class="flex flex-col items-center justify-center p-16 bg-white rounded-[3rem] shadow-sm border border-gray-100 text-center space-y-6">
                <div class="h-24 w-24 bg-rose-50 rounded-full flex items-center justify-center text-rose-500 ring-8 ring-rose-50/50">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                </div>
                <div class="max-w-md">
                    <h2 class="text-2xl font-black text-gray-900 uppercase tracking-tight">Status Offline</h2>
                    <p class="text-gray-500 font-medium mt-3">Anda sedang dalam mode tidak tersedia. Aktifkan status Anda untuk mulai menerima penugasan baru.</p>
                </div>
            </div>
        @else
            {{-- Banner Bekerja --}}
            @if($sopir->status === 'Bekerja')
                <div class="bg-gradient-to-r from-cyan-600 to-blue-600 rounded-[2rem] p-8 text-white shadow-xl shadow-cyan-100 mb-10 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-black uppercase tracking-tight">Tugas Sedang Berjalan</h3>
                        <p class="text-cyan-50 text-sm font-medium opacity-90 mt-1">Gunakan logbook harian untuk mencatat aktivitas perjalanan Anda.</p>
                    </div>
                    <a href="{{ route('logbook') }}" class="bg-white text-cyan-600 font-black py-3 px-8 rounded-xl uppercase text-[10px] tracking-widest hover:bg-cyan-50">Logbook Saya</a>
                </div>
            @endif

            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:border-cyan-200 transition-all">
                    <div class="flex justify-between items-start mb-6">
                        <div class="h-12 w-12 bg-cyan-50 rounded-2xl flex items-center justify-center text-cyan-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        </div>
                        <span class="text-[9px] font-black uppercase bg-cyan-50 text-cyan-600 px-3 py-1 rounded-full">Aktif</span>
                    </div>
                    <div class="text-4xl font-black text-gray-900">{{ $tugasAktifCount }}</div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-2">Penugasan Saat Ini</p>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:border-emerald-200 transition-all">
                    <div class="flex justify-between items-start mb-6">
                        <div class="h-12 w-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="text-[9px] font-black uppercase bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full">History</span>
                    </div>
                    <div class="text-4xl font-black text-gray-900">{{ $riwayatSelesaiCount }}</div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-2">Tugas Selesai</p>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm hover:border-blue-200 transition-all">
                    <div class="flex justify-between items-start mb-6">
                        <div class="h-12 w-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <span class="text-[9px] font-black uppercase bg-blue-50 text-blue-600 px-3 py-1 rounded-full">Status</span>
                    </div>
                    <div class="text-2xl font-black text-gray-900 uppercase tracking-tight">{{ $sopir->status }}</div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-2">Kondisi Akun</p>
                </div>
            </div>
        @endif
    @else
        <div class="bg-amber-50 p-8 rounded-[2rem] border border-amber-100 flex items-center gap-4 text-amber-800 font-bold">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            Profil Sopir Anda belum terdaftar di sistem. Mohon hubungi admin.
        </div>
    @endif

    <x-toast-notification />
</div>