<nav
x-cloak
class="absolute inset-y-0 left-0 z-30 bg-cyan-800 text-cyan-50 overflow-y-auto
transition-all duration-300 transform
lg:static lg:translate-x-0 lg:flex-shrink-0 shadow-xl"
:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
:style="sidebarOpen ? 'width: 16rem;' : 'width: 5rem;'"
>

{{-- Header Logo --}}
<div class="flex items-center justify-center h-16 bg-cyan-900 shadow-md p-3 overflow-hidden">
    <span x-show="sidebarOpen"
          x-transition
          class="text-xl font-bold tracking-wider whitespace-nowrap">
        AKA RENTAL
    </span>

    <span x-show="!sidebarOpen"
          x-transition
          class="text-xl font-black tracking-wider">
        AK
    </span>
</div>

<div class="py-4 space-y-2">

    {{-- 1. DASHBOARD (Umum untuk semua user login) --}}
    <div class="px-4 mt-2 mb-1 text-[10px] font-black text-cyan-400 uppercase tracking-widest" x-show="sidebarOpen">
        Menu Utama
    </div>
    
    <a href="{{ route('home') }}"
       class="flex items-center px-4 py-3 transition-all duration-200 
              {{ request()->routeIs('home') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700' }}"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Dashboard">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l-2-2m-2 2h-4m-7 20h14a1 1 0 001-1V12a1 1 0 00-1-1H5a1 1 0 00-1 1v7a1 1 0 001 1z"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Dashboard</span>
    </a>

    {{-- 2. MASTER ARMADA --}}
    @can('read-mobils')
    <div class="px-4 mt-6 mb-1 text-[10px] font-black text-cyan-400 uppercase tracking-widest" x-show="sidebarOpen">
        Armada
    </div>

    <a href="{{ route('mobil') }}"
       class="flex items-center px-4 py-3 transition-all duration-200 
              {{ request()->routeIs('mobil') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700' }}"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Data Mobil">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Manajemen Mobil</span>
    </a>
    @endcan

    {{-- 3. PELANGGAN & VERIFIKASI --}}
    @if(Auth::user()->can('read-users') || Auth::user()->can('read-user_identifications'))
    <div class="px-4 mt-6 mb-1 text-[10px] font-black text-cyan-400 uppercase tracking-widest" x-show="sidebarOpen">
        Pelanggan
    </div>
    
  @can('read-roles')
    <a href="{{ route('roles') }}"
       class="flex items-center px-4 py-3 transition-all duration-200 
              {{ request()->routeIs('roles') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700' }}"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Hak Akses">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Hak Akses</span>
    </a>
    @endcan

    @can('read-users')
    <a href="{{ route('users') }}"
       class="flex items-center px-4 py-3 transition-all duration-200 
              {{ request()->routeIs('users') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700' }}"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Daftar Pengguna">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Semua User</span>
    </a>
    @endcan

    @can('read-user_identifications')
    <a href="{{ route('verifikasi') }}"
       class="flex items-center px-4 py-3 transition-all duration-200 
              {{ request()->routeIs('verifikasi') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700' }}"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Verifikasi KTP">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Verifikasi KTP</span>
    </a>
    @endcan
    @endif

    {{-- 4. TRANSAKSI SEWA --}}
    @if(Auth::user()->can('read-peminjaman') || Auth::user()->can('read-pengembalian') || Auth::user()->can('read-pembatalan_pesanan') || Auth::user()->can('read-payment_transactions'))
    <div class="px-4 mt-6 mb-1 text-[10px] font-black text-cyan-400 uppercase tracking-widest" x-show="sidebarOpen">
        Penyewaan
    </div>

    @can('read-peminjaman')
    <a href="{{ route('peminjaman') }}"
       class="flex items-center px-4 py-3 transition-all duration-200 
              {{ request()->routeIs('peminjaman') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700' }}"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Sewa Kendaraan">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Peminjaman</span>
    </a>
    @endcan

    @can('read-pengembalian')
    <a href="{{ route('pengembalian') }}"
       class="flex items-center px-4 py-3 transition-all duration-200 
              {{ request()->routeIs('pengembalian') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700' }}"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Pengembalian">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Pengembalian</span>
    </a>
    @endcan

    @can('read-pembatalan_pesanan')
    <a href="{{ route('pembatalan') }}"
       class="flex items-center px-4 py-3 transition-all duration-200 
              {{ request()->routeIs('pembatalan') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700' }}"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Pembatalan">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Pembatalan</span>
    </a>
    @endcan

    @can('read-payment_transactions')
    <a href="#"
       class="flex items-center px-4 py-3 transition-all duration-200 hover:bg-cyan-700"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Transaksi Keuangan">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Pembayaran</span>
    </a>
    @endcan
    @endif

    {{-- 5. OPERASIONAL & KONDISI --}}
    @if(Auth::user()->can('read-vehicle_inspections') || Auth::user()->can('read-vehicle_damage_reports') || Auth::user()->can('read-driver_logbooks') || Auth::user()->can('read-fines'))
    <div class="px-4 mt-6 mb-1 text-[10px] font-black text-cyan-400 uppercase tracking-widest" x-show="sidebarOpen">
        Operasional
    </div>

    @can('read-vehicle_inspections')
    <a href="{{ route('inspeksi') }}"
       class="flex items-center px-4 py-3 transition-all duration-200 
              {{ request()->routeIs('inspeksi') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700' }}"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Inspeksi Kendaraan">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Pengecekan Mobil</span>
    </a>
    @endcan

    @can('read-vehicle_damage_reports')
    <a href="{{ route('damage-report') }}"
       class="flex items-center px-4 py-3 transition-all duration-200 
              {{ request()->routeIs('damage-report') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700' }}"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Laporan Kerusakan">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Laporan Rusak</span>
    </a>
    @endcan

    @can('read-driver_logbooks')
    <a href="{{ route('logbook') }}"
       class="flex items-center px-4 py-3 transition-all duration-200 
              {{ request()->routeIs('logbook') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700' }}"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Logbook Sopir">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Logbook Sopir</span>
    </a>
    @endcan

    @can('read-fines')
    <a href="#"
       class="flex items-center px-4 py-3 transition-all duration-200 hover:bg-cyan-700"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Data Denda">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Sanksi & Denda</span>
    </a>
    @endcan
    @endif

    {{-- 6. SDM & AKSES SISTEM --}}
    @if(Auth::user()->can('read-roles') || Auth::user()->can('read-sopirs') || Auth::user()->can('read-staffs') || Auth::user()->can('read-resepsionis'))
    <div class="px-4 mt-6 mb-1 text-[10px] font-black text-cyan-400 uppercase tracking-widest" x-show="sidebarOpen">
        Manajemen SDM
    </div>

  

    @can('read-sopirs')
    <a href="{{ route('sopir') }}"
       class="flex items-center px-4 py-3 transition-all duration-200 
              {{ request()->routeIs('sopir') ? 'bg-cyan-700 border-l-4 border-cyan-400' : 'hover:bg-cyan-700' }}"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Data Sopir">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Daftar Sopir</span>
    </a>
    @endcan

    @can('read-staffs')
    <a href="#"
       class="flex items-center px-4 py-3 transition-all duration-200 hover:bg-cyan-700"
       :class="sidebarOpen ? 'justify-start' : 'justify-center'"
       title="Data Staff">
        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <span x-show="sidebarOpen" x-transition class="ml-3 text-sm font-medium whitespace-nowrap uppercase italic tracking-tighter">Tim Staff</span>
    </a>
    @endcan
    @endif

</div>


</nav>

{{-- MOBILE OVERLAY --}}

<div
x-show="sidebarOpen"
x-transition.opacity
class="fixed inset-0 z-20 bg-cyan-900 bg-opacity-50 lg:hidden"
@click="sidebarOpen = false">
</div>