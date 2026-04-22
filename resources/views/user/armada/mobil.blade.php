<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Mobil') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto mt-10 mb-16 px-6 py-10 bg-white rounded-3xl shadow-sm border border-gray-100">

        {{-- 🔹 Flash Messages --}}
        @if (session('success'))
            <x-alert type="success">{{ session('success') }}</x-alert>
        @endif
        @if (session('warning'))
            <x-alert type="warning">{{ session('warning') }}</x-alert>
        @endif
        @if (session('error'))
            <x-alert type="error">{{ session('error') }}</x-alert>
        @endif

        {{-- 🔍 Search & Filter Section --}}
        <div class="mb-10">
            <div class="flex flex-col md:flex-row gap-4 items-stretch bg-gray-50 p-4 rounded-2xl border border-gray-100">
                
                {{-- Search Bar --}}
                <div class="flex-grow">
                    <div class="relative h-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <x-text-input 
                            id="searchInput" 
                            type="text" 
                            placeholder="Cari nama atau tipe mobil..." 
                            class="w-full h-full pl-10 border-gray-200 focus:border-cyan-500 focus:ring-cyan-500 rounded-xl shadow-sm py-2.5"
                        />
                    </div>
                </div>

                {{-- 🔽 Filter Dropdown --}}
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-stretch">
                    {{-- Jumlah Kursi --}}
                    <div x-data="{ open: false }" class="relative w-full sm:w-48">
                        <button 
                            @click="open = !open"
                            type="button"
                            class="flex items-center justify-between w-full px-4 py-2.5 rounded-xl font-medium transition h-full shadow-sm {{ request('jumlah_kursi') ? 'bg-cyan-50 border-cyan-400 text-cyan-700' : 'bg-white border-gray-200 text-gray-700 hover:border-cyan-500 hover:text-cyan-600' }}"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 {{ request('jumlah_kursi') ? 'text-cyan-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                @if(request('jumlah_kursi'))
                                    {{ request('jumlah_kursi') }} Kursi
                                @else
                                    Pilih Kursi
                                @endif
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute left-0 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-lg z-20 overflow-hidden" style="display: none;">
                            @foreach(['5', '7', '9', '14', '19'] as $kursi)
                                <a href="{{ request()->fullUrlWithQuery(['jumlah_kursi' => $kursi, 'page' => null]) }}" 
                                   class="block px-4 py-2 text-sm {{ request('jumlah_kursi') == $kursi ? 'bg-cyan-50 text-cyan-700 font-bold' : 'text-gray-700 hover:bg-cyan-50 hover:text-cyan-700' }}">
                                   {{ $kursi }} Kursi
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Transmisi --}}
                    <div x-data="{ open: false }" class="relative w-full sm:w-48">
                        <button 
                            @click="open = !open"
                            type="button"
                            class="flex items-center justify-between w-full px-4 py-2.5 rounded-xl font-medium transition h-full shadow-sm {{ request('transmisi') ? 'bg-cyan-50 border-cyan-400 text-cyan-700' : 'bg-white border-gray-200 text-gray-700 hover:border-cyan-500 hover:text-cyan-600' }}"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 {{ request('transmisi') ? 'text-cyan-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                @if(request('transmisi'))
                                    {{ ucfirst(request('transmisi')) }}
                                @else
                                    Transmisi
                                @endif
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute left-0 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-lg z-20 overflow-hidden" style="display: none;">
                            <a href="{{ request()->fullUrlWithQuery(['transmisi' => 'manual', 'page' => null]) }}" 
                               class="block px-4 py-2 text-sm {{ request('transmisi') == 'manual' ? 'bg-cyan-50 text-cyan-700 font-bold' : 'text-gray-700 hover:bg-cyan-50 hover:text-cyan-700' }}">Manual</a>
                            <a href="{{ request()->fullUrlWithQuery(['transmisi' => 'otomatis', 'page' => null]) }}" 
                               class="block px-4 py-2 text-sm {{ request('transmisi') == 'otomatis' ? 'bg-cyan-50 text-cyan-700 font-bold' : 'text-gray-700 hover:bg-cyan-50 hover:text-cyan-700' }}">Automatic</a>
                        </div>
                    </div>

                    {{-- Tombol Reset --}}
                    <div class="flex-none">
                        <a href="{{ route('mobils.index') }}"
                            class="w-full h-full px-6 py-2.5 bg-gray-200 text-gray-700 hover:bg-gray-300 font-bold rounded-xl flex items-center justify-center whitespace-nowrap transition-colors"
                        >
                            Reset Filter
                        </a>
                    </div>
                </div>
            </div>

            {{-- 🔹 Menampilkan Indikator Filter yang Sedang Aktif --}}
            @if(request('jumlah_kursi') || request('transmisi'))
                <div class="flex flex-wrap items-center gap-2 mt-4 px-2">
                    <span class="text-sm font-medium text-gray-500">Filter Aktif:</span>
                    
                    @if(request('jumlah_kursi'))
                        <a href="{{ request()->fullUrlWithQuery(['jumlah_kursi' => null, 'page' => null]) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-cyan-700 bg-cyan-100 rounded-full hover:bg-cyan-200 hover:text-cyan-800 transition-colors shadow-sm">
                            {{ request('jumlah_kursi') }} Kursi
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </a>
                    @endif

                    @if(request('transmisi'))
                        <a href="{{ request()->fullUrlWithQuery(['transmisi' => null, 'page' => null]) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-cyan-700 bg-cyan-100 rounded-full hover:bg-cyan-200 hover:text-cyan-800 transition-colors shadow-sm">
                            Transmisi {{ ucfirst(request('transmisi')) }}
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- 🔹 Daftar Mobil --}}
        <section id="daftar-mobil">
            @if ($mobils->isEmpty())
                <div class="text-center py-20 bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto shadow-sm mb-4">
                        <span class="text-4xl">🚗</span>
                    </div>
                    <p class="text-gray-500 text-lg font-medium">Tidak ada mobil yang tersedia dengan filter tersebut.</p>
                </div>
            @else
                <div id="noResultMessage" class="hidden text-center py-20 bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                    <p class="text-gray-500 text-lg font-medium">🚗 Mobil yang dicari tidak ditemukan.</p>
                </div>

                <div id="mobilList" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($mobils as $mobil)
                        <div class="mobil-card group bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transform-gpu hover:-translate-y-2 transition-all duration-300 flex flex-col">
                            
                            {{-- Image Container --}}
                            <div class="relative overflow-hidden h-56 bg-gray-100 shrink-0">
                                <img src="{{ asset('storage/' . $mobil->foto) }}" alt="{{ $mobil->tipe }}" loading="lazy" class="w-full h-full object-cover transform-gpu group-hover:scale-105 transition-transform duration-500 ease-out">
                                {{-- Gradient Overlay --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                                
                                {{-- Badges --}}
                                <div class="absolute bottom-4 left-4 right-4 flex justify-between items-end">
                                    <div class="bg-white text-gray-900 text-sm font-bold px-4 py-2 rounded-xl shadow-sm">
                                        Rp {{ number_format($mobil->harga, 0, ',', '.') }} <span class="text-xs font-medium text-gray-500">/ hari</span>
                                    </div>
                                    <div class="bg-cyan-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                                        Tersedia
                                    </div>
                                </div>
                            </div>

                            {{-- Detail Container --}}
                            <div class="p-6 flex-1 flex flex-col">
                                <h3 class="text-2xl font-bold text-gray-800 group-hover:text-cyan-600 transition-colors">{{ $mobil->tipe }}</h3>
                                <p class="text-sm font-medium text-cyan-600 mb-4">{{ $mobil->merek }}</p>

                                {{-- Grid Fitur --}}
                                <div class="grid grid-cols-2 gap-y-3 gap-x-2 text-sm text-gray-600 mb-6 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="p-1.5 bg-gray-50 rounded-lg text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                                        </span>
                                        <span class="font-medium">{{ $mobil->warna }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="p-1.5 bg-gray-50 rounded-lg text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        </span>
                                        <span class="font-medium capitalize">{{ $mobil->transmisi }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="p-1.5 bg-gray-50 rounded-lg text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        </span>
                                        <span class="font-medium">{{ $mobil->kursi }} Kursi</span>
                                    </div>
                                </div>

                                {{-- Buttons Action --}}
                                <div class="mt-auto">
                                    @auth
                                        @if ($hasIdentification)
                                            {{-- ✅ Sudah upload identitas --}}
                                            <a href="{{ route('peminjaman.create', $mobil->id) }}"
                                               class="flex items-center justify-center w-full bg-cyan-50 text-cyan-700 border border-cyan-100 group-hover:bg-cyan-500 group-hover:text-white group-hover:border-cyan-500 px-4 py-3 rounded-xl font-bold transition-all duration-300">
                                                Sewa Sekarang 
                                                <svg class="w-4 h-4 ml-2 transform-gpu group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                            </a>
                                        @else
                                            {{-- ⚠️ Belum upload identitas --}}
                                            <a href="{{ route('upload.identity') }}"
                                               class="flex items-center justify-center w-full bg-amber-50 text-amber-700 border border-amber-100 hover:bg-amber-500 hover:text-white px-4 py-3 rounded-xl font-bold transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                Lengkapi Identitas
                                            </a>
                                        @endif
                                    @else
                                        {{-- 🔒 Belum login --}}
                                        <a href="{{ route('login') }}"
                                           class="flex items-center justify-center w-full bg-gray-50 text-gray-700 border border-gray-200 hover:bg-gray-800 hover:text-white hover:border-gray-800 px-4 py-3 rounded-xl font-bold transition-colors duration-200">
                                            Login untuk Meminjam
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- 🔸 Pagination --}}
                <div class="mt-12">
                    {{ $mobils->links('components.pagination-info') }}
                </div>
            @endif
        </section>
    </div>

    {{-- 🔹 Script Pencarian --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('searchInput');
            const cards = document.querySelectorAll('.mobil-card');
            const message = document.getElementById('noResultMessage');

            if(input) {
                input.addEventListener('keyup', () => {
                    const value = input.value.toLowerCase();
                    let hasResult = false;

                    cards.forEach(card => {
                        const searchContent = card.querySelector('h3').innerText.toLowerCase() + " " + card.querySelector('p').innerText.toLowerCase();
                        const match = searchContent.includes(value);
                        
                        card.style.display = match ? '' : 'none';
                        if (match) hasResult = true;
                    });

                    if(message) {
                        message.classList.toggle('hidden', hasResult);
                    }
                });
            }
        });
    </script>
</x-app-layout>