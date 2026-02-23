<div>
    <nav
        x-cloak
        class="fixed inset-y-0 left-0 z-30 h-screen flex flex-col bg-cyan-800 text-cyan-50 
               transition-all duration-300 ease-in-out shadow-xl lg:static lg:translate-x-0"
        :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-20 -translate-x-full lg:translate-x-0'"
    >

        {{-- Header Logo (Fixed at top) --}}
        <div class="flex items-center h-16 bg-cyan-900 shadow-md px-6 overflow-hidden flex-shrink-0">
            <div class="flex items-center min-w-[200px]">
                <span class="text-xl font-black tracking-wider flex-shrink-0">AK</span>
                <span x-show="sidebarOpen" 
                      x-transition:enter="transition ease-out duration-300"
                      x-transition:enter-start="opacity-0 -translate-x-2"
                      x-transition:enter-end="opacity-100 translate-x-0"
                      x-transition:leave="transition ease-in duration-100"
                      x-transition:leave-start="opacity-100"
                      x-transition:leave-end="opacity-0"
                      class="ml-1 text-xl font-bold tracking-wider whitespace-nowrap uppercase">
                    A RENTAL
                </span>
            </div>
        </div>

        {{-- Area Konten (Scrollable & Flex-1 agar mengisi sisa ruang bawah) --}}
        <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar pb-6">
            
            {{-- ========================================================================= --}}
            {{-- INJEKSI: PANEL STATUS SOPIR --}}
            {{-- ========================================================================= --}}
            @can('menu_panel_status_sopir')
            @if(isset($sopir) && $sopir)
            <div x-show="sidebarOpen" class="px-4 py-3 border-t border-b border-cyan-700/50 mx-4 mt-4 rounded-lg bg-cyan-700/30 backdrop-blur-sm shadow-inner">
                <p class="text-[10px] font-black text-cyan-400 uppercase tracking-widest mb-2 flex justify-between items-center">
                    Status Anda
                    <span class="h-2 w-2 rounded-full animate-ping {{ strtolower(trim(str_replace('_', ' ', $sopir->status))) === 'tersedia' ? 'bg-green-400' : (strtolower(trim(str_replace('_', ' ', $sopir->status))) === 'bekerja' ? 'bg-blue-400' : 'bg-red-400') }}"></span>
                </p>

                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white uppercase tracking-tight">
                        {{ str_replace('_', ' ', $sopir->status) }}
                    </span>

                    @php
                        // Menyatukan format text (menghapus spasi lebih & underscore) agar logika switch sempurna
                        $statSopir = strtolower(trim(str_replace('_', ' ', $sopir->status)));
                        $isChecked = in_array($statSopir, ['tersedia', 'bekerja']);
                        $isLocked = ($statSopir === 'bekerja');
                    @endphp

                    <label class="relative inline-flex items-center cursor-pointer {{ $isLocked ? 'opacity-50 pointer-events-none' : '' }}">
                        <input type="checkbox" class="sr-only peer" 
                               {{ $isChecked ? 'checked' : '' }}
                               {{ $isLocked ? 'disabled' : '' }}
                               wire:click="toggleStatus"
                               wire:loading.attr="disabled">
                        <div class="relative w-9 h-5 bg-gray-600 rounded-full peer peer-checked:bg-green-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-full duration-300 ease-in-out"></div>
                    </label>
                </div>
                @if($isLocked)
                    <p class="text-[9px] text-cyan-200/70 mt-1 italic tracking-tight">🔒 Terkunci saat bertugas</p>
                @endif
            </div>
            @endif
            @endcan
            {{-- ========================================================================= --}}

            <div class="py-4 space-y-1">

                {{-- 1. DASHBOARD (Satu Menu Dinamis dengan 3 Kondisi) --}}
                @canany(['menu_dashboard', 'menu_dashboard_sopir', 'menu_dashboard_staff'])
                <div class="px-6 mt-2 mb-1 h-4">
                    <p x-show="sidebarOpen" 
                       x-transition:enter="transition opacity ease-out duration-300"
                       class="text-[10px] font-black text-cyan-400 uppercase tracking-widest whitespace-nowrap">
                       Menu Utama
                    </p>
                </div>
                
                @can('menu_dashboard')
                <a href="{{ route('home') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('home') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Dashboard">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l-2-2m-2 2h-4m-7 20h14a1 1 0 001-1V12a1 1 0 00-1-1H5a1 1 0 00-1 1v7a1 1 0 001 1z"></path>
                    </svg>
                    <span x-show="sidebarOpen" 
                          x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Dashboard Admin
                    </span>
                </a>
                @elsecan('menu_dashboard_sopir')
                <a href="{{ route('sopir.dashboard') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('sopir.dashboard') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Dashboard Sopir">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    <span x-show="sidebarOpen" 
                          x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Dashboard Sopir
                    </span>
                </a>
                @elsecan('menu_dashboard_staff')
                <a href="{{ route('staff.dashboard') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('staff.dashboard') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Dashboard Staff">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span x-show="sidebarOpen" 
                          x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Dashboard Staff
                    </span>
                </a>
                @endcan
                @endcanany

                {{-- 2. MASTER ARMADA --}}
                @can('menu_mobil')
                <div class="px-6 mt-6 mb-1 h-4">
                    <p x-show="sidebarOpen" x-transition:enter="transition opacity ease-out duration-300"
                       class="text-[10px] font-black text-cyan-400 uppercase tracking-widest whitespace-nowrap">
                       Armada
                    </p>
                </div>

                <a href="{{ route('mobil') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('mobil') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Data Mobil">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Manajemen Mobil
                    </span>
                </a>
                @endcan

                {{-- 3. PELANGGAN & VERIFIKASI --}}
                @canany(['menu_hak_akses', 'menu_pengguna', 'menu_pelanggan', 'menu_verifikasi_ktp'])
                <div class="px-6 mt-6 mb-1 h-4">
                    <p x-show="sidebarOpen" x-transition:enter="transition opacity ease-out duration-300"
                       class="text-[10px] font-black text-cyan-400 uppercase tracking-widest whitespace-nowrap">
                       Pelanggan
                    </p>
                </div>
                
                @can('menu_hak_akses')
                <a href="{{ route('roles') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('roles') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Hak Akses">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Hak Akses
                    </span>
                </a>
                @endcan

                @can('menu_pengguna')
                <a href="{{ route('users') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('users') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Daftar Pengguna">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Semua User
                    </span>
                </a>
                @endcan

                @can('menu_pelanggan')
                <a href="{{ route('pelanggan') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('pelanggan') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Data Pelanggan">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen" 
                          x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Data Pelanggan
                    </span>
                </a>
                @endcan

                @can('menu_verifikasi_ktp')
                <a href="{{ route('verifikasi') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('verifikasi') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Verifikasi KTP">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Verifikasi KTP
                    </span>
                </a>
                @endcan
                @endcanany

                {{-- 4. TRANSAKSI SEWA --}}
                @canany(['menu_peminjaman', 'menu_pengembalian', 'menu_pembatalan', 'menu_pembayaran'])
                <div class="px-6 mt-6 mb-1 h-4">
                    <p x-show="sidebarOpen" x-transition:enter="transition opacity ease-out duration-300"
                       class="text-[10px] font-black text-cyan-400 uppercase tracking-widest whitespace-nowrap">
                       Penyewaan
                    </p>
                </div>

                @can('menu_peminjaman')
                <a href="{{ route('peminjaman') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('peminjaman') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Sewa Kendaraan">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Peminjaman
                    </span>
                </a>
                @endcan

                @can('menu_pengembalian')
                <a href="{{ route('pengembalian') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('pengembalian') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Pengembalian">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Pengembalian
                    </span>
                </a>
                @endcan

                @can('menu_pembatalan')
                <a href="{{ route('pembatalan') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('pembatalan') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Pembatalan">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Pembatalan
                    </span>
                </a>
                @endcan

                @can('menu_pembayaran')
                <a href="{{ route('pembayaran') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('pembayaran') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Pembayaran">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Pembayaran
                    </span>
                </a>
                @endcan
                @endcanany

                {{-- 5. OPERASIONAL & KONDISI --}}
                @canany(['menu_inspeksi', 'menu_laporan_kerusakan', 'menu_logbook_sopir', 'menu_logbook_admin', 'menu_sanksi_denda'])
                <div class="px-6 mt-6 mb-1 h-4">
                    <p x-show="sidebarOpen" x-transition:enter="transition opacity ease-out duration-300"
                       class="text-[10px] font-black text-cyan-400 uppercase tracking-widest whitespace-nowrap">
                       Operasional
                    </p>
                </div>

                @can('menu_inspeksi')
                <a href="{{ route('inspeksi') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('inspeksi') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Inspeksi Kendaraan">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Pengecekan Mobil
                    </span>
                </a>
                @endcan

                @can('menu_laporan_kerusakan')
                <a href="{{ route('damage-report') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('damage-report') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Laporan Kerusakan">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Laporan Rusak
                    </span>
                </a>
                @endcan

                @can('menu_logbook_sopir')
                <a href="{{ route('logbook') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('logbook') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Logbook Sopir">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Logbook Saya
                    </span>
                </a>
                @endcan

                @can('menu_logbook_admin')
                <a href="{{ route('logbook') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('logbook') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Data Logbook">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Data Logbook
                    </span>
                </a>
                @endcan

                @can('menu_sanksi_denda')
                <a href="{{ route('fines') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('fines') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Data Denda">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Sanksi & Denda
                    </span>
                </a>
                @endcan
                @endcanany

                {{-- 6. SDM & AKSES SISTEM --}}
                @canany(['menu_resepsionis', 'menu_daftar_sopir', 'menu_tim_staff'])
                <div class="px-6 mt-6 mb-1 h-4">
                    <p x-show="sidebarOpen" x-transition:enter="transition opacity ease-out duration-300"
                       class="text-[10px] font-black text-cyan-400 uppercase tracking-widest whitespace-nowrap">
                       Manajemen SDM
                    </p>
                </div>

                @can('menu_resepsionis')
                <a href="{{ route('resepsionis') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('resepsionis') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Data Resepsionis">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Data Resepsionis
                    </span>
                </a>
                @endcan

                @can('menu_daftar_sopir')
                <a href="{{ route('sopir') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 
                          {{ request()->routeIs('sopir') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700 border-l-4 border-transparent' }}"
                   title="Data Sopir">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Daftar Sopir
                    </span>
                </a>
                @endcan

                @can('menu_tim_staff')
                <a href="{{ route('staff') }}" wire:navigate
                   class="group flex items-center px-6 py-3 transition-all duration-200 hover:bg-cyan-700 border-l-4 border-transparent"
                   title="Data Staff">
                    <svg class="h-6 w-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300 delay-100"
                          x-transition:enter-start="opacity-0 translate-x-2"
                          x-transition:enter-end="opacity-100 translate-x-0"
                          class="ml-4 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">
                        Tim Staff
                    </span>
                </a>
                @endcan
                @endcanany

            </div>
        </div> {{-- End of Scrollable Area --}}
    </nav>

    {{-- MOBILE OVERLAY --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-20 bg-cyan-900/50 lg:hidden"
        @click="sidebarOpen = false">
    </div>
</div>