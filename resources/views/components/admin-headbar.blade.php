<header class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">

{{-- BAGIAN KIRI --}}
<div class="flex items-center">

    {{-- Tombol Hamburger (Toggle Sidebar) --}}
    <button 
        @click="sidebarOpen = !sidebarOpen"
        class="text-gray-500 hover:text-cyan-700 focus:outline-none mr-4 transition-colors p-2 rounded-lg hover:bg-gray-100"
    >
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    {{-- Judul Halaman Dinamis (Berdasarkan Route Name) --}}
    <h1 class="text-lg font-bold text-gray-800 hidden md:block tracking-tight">
        @switch(Route::currentRouteName())
            @case('home')           Dashboard Utama @break
            @case('users')          Manajemen Pengguna @break
            @case('roles')          Pengaturan Hak Akses @break
            @case('mobil')          Manajemen Armada @break
            @case('sopir')          Data Driver @break
            @case('staff')          Data Staff Operasional @break
            @case('resepsionis')    Data Resepsionis @break
            @case('verifikasi')     Verifikasi Identitas Pelanggan @break
            @case('peminjaman')     Transaksi Peminjaman @break
            @case('pengembalian')   Proses Pengembalian @break
            @case('pembatalan')     Persetujuan Pembatalan @break
            @case('logbook')        Log Aktivitas Driver @break
            @case('inspeksi')       Pengecekan Kendaraan @break
            @case('damage-report')  Laporan Kerusakan Kendaraan @break
            @case('profile')        Profil Saya @break
            @default                AKA RENTAL System
        @endswitch
    </h1>
</div>

{{-- BAGIAN KANAN: DROPDOWN USER --}}
<div x-data="{ dropdownOpen: false }" class="relative">

    <button 
        @click="dropdownOpen = !dropdownOpen" 
        @keydown.escape="dropdownOpen = false"
        class="flex items-center gap-3 p-1 px-2 rounded-full hover:bg-gray-50 transition-all focus:outline-none border border-transparent hover:border-gray-200"
    >
        <div class="text-right hidden sm:block">
            <p class="text-xs font-bold text-gray-800 leading-none">
                {{ Auth::user()->name ?? 'Administrator' }}
            </p>
            <p class="text-[10px] text-cyan-600 font-semibold uppercase tracking-tighter mt-1">
                {{ Auth::user()->getRoleNames()->first() ?? 'User' }}
            </p>
        </div>

        <div class="h-9 w-9 rounded-full bg-cyan-700 flex items-center justify-center text-white font-bold shadow-sm ring-2 ring-white ring-offset-1">
            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
        </div>
    </button>

    {{-- Dropdown Menu --}}
    <div 
        x-show="dropdownOpen"
        @click.outside="dropdownOpen = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl z-50 border border-gray-100 py-2"
        style="display: none;" 
    >
        <div class="px-4 py-2 border-b border-gray-50 mb-1 md:hidden">
            <p class="text-sm font-bold text-gray-800">{{ Auth::user()->name }}</p>
            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
        </div>

        {{-- Profile Link --}}
        <a href="{{ route('profile') }}"
           class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-cyan-50 hover:text-cyan-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
            Profil Saya
        </a>

        <div class="h-px bg-gray-100 my-1"></div>

        {{-- Logout Button --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button 
                onclick="event.preventDefault(); this.closest('form').submit();"
                class="w-full flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors font-medium"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                Keluar Sistem
            </button>
        </form>
    </div>

</div>


</header>