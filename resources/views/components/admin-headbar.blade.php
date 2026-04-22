<header class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">

    {{-- BAGIAN KIRI --}}
    <div class="flex items-center">
        {{-- Tombol Hamburger (Toggle Sidebar) --}}
        <button 
            @click="sidebarOpen = !sidebarOpen"
            class="text-gray-500 hover:text-cyan-700 focus:outline-none mr-4 transition-colors p-2 rounded-lg hover:bg-gray-100"
        >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        {{-- Judul Halaman Dinamis --}}
        <h1 class="text-lg font-bold text-gray-800 hidden md:block tracking-tight">
            @switch(Route::currentRouteName())
                @case('home') Dashboard Utama @break
                @case('users') Manajemen Pengguna @break
                @case('pelanggan') Manajemen Pelanggan @break
                @case('roles') Pengaturan Hak Akses @break
                @case('mobil') Manajemen Armada @break
                @case('sopir') Data Driver @break
                @case('staff') Data Staff Operasional @break
                @case('resepsionis') Data Resepsionis @break
                @case('verifikasi') Verifikasi Identitas Pelanggan @break
                @case('peminjaman') Transaksi Peminjaman @break
                @case('pengembalian') Proses Pengembalian @break
                @case('pembayaran') Data Transaksi Pembayaran @break
                @case('pembatalan') Persetujuan Pembatalan @break
                @case('logbook') Log Aktivitas Driver @break
                @case('inspeksi') Pengecekan Kendaraan @break
                @case('damage-report') Laporan Kerusakan Kendaraan @break
                @case('resepsionis.livechat') Livechat Pelanggan @break {{-- Tambahan Judul Halaman Chat --}}
                @default Sistem Informasi Aka Rental
            @endswitch
        </h1>
    </div>

    {{-- BAGIAN KANAN: PROFIL & LOGOUT --}}
    <div class="flex items-center gap-4">
        
        {{-- TOMBOL LIVECHAT (Hanya untuk Admin & Resepsionis) --}}
        @hasanyrole('admin|resepsionis')
        <a href="{{ route('resepsionis.livechat') }}" 
           class="relative p-2 text-gray-400 hover:text-cyan-600 transition-colors hover:bg-cyan-50 rounded-full" 
           title="Livechat Pelanggan">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
            {{-- Indikator Titik Merah (Opsional) --}}
            <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-rose-500 border-2 border-white rounded-full"></span>
        </a>
        @endhasanyrole

        <div class="h-6 w-px bg-gray-200 hidden sm:block"></div>

        <div class="flex items-center gap-3">
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
        </div>

        <div class="h-6 w-px bg-gray-200"></div>

        {{-- Tombol Logout --}}
        <form method="POST" action="{{ route('logout') }}" id="logout-form">
            @csrf
            <button 
                type="button"
                onclick="confirmLogout()"
                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-all group"
                title="Keluar Sistem"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </button>
        </form>
    </div>

</header>

{{-- Load SweetAlert2 dari CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Keluar Sistem?',
            text: "Apakah Anda yakin ingin mengakhiri sesi ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0e7490', // cyan-700
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Keluar!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Menjalankan submit form jika dikonfirmasi
                document.getElementById('logout-form').submit();
            }
        })
    }
</script>