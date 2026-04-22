<head>
    @php
        $isProduction = app()->environment('production');
        $manifestPath = $isProduction ? '../public_html/build/manifest.json' : public_path('build/manifest.json');
    @endphp

    @if ($isProduction && file_exists($manifestPath))
        @php
            $manifest = json_decode(file_get_contents($manifestPath), true);
        @endphp
        <link rel="stylesheet" href="{{ config('app.url') }}/build/{{ $manifest['resources/css/app.css']['file'] }}">
        <script type="module" src="{{ config('app.url') }}/build/{{ $manifest['resources/js/app.js']['file'] }}"></script>
    @else
        @viteReactRefresh
        @vite(['resources/js/app.js', 'resources/css/app.css'])
    @endif
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<nav x-data="{ open: false }" class="bg-white shadow-md fixed w-full top-0 left-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
        <!-- 🔹 Logo -->
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <img src="{{ asset('logoakarentcar.png') }}" alt="Aka Rent Car" class="h-12 w-auto object-contain" />
            <span class="sr-only">Aka Rent Car</span>
        </a>

        <!-- 🔹 Menu Desktop -->
        <ul class="hidden md:flex items-center space-x-8 text-sm font-semibold text-gray-800">
            <li><a href="{{ route('dashboard') }}" class="hover:text-cyan-600 transition">Dashboard</a></li>
            <li><a href="{{ route('mobils.index') }}" class="hover:text-cyan-600 transition">Armada</a></li>
            <li><a href="{{ route('pesanan.saya') }}" class="hover:text-cyan-600 transition">Pesanan Saya</a></li>

            @auth
                <!-- 🚀 MEMANGGIL KOMPONEN LIVEWIRE NOTIFIKASI 🚀 -->
                <livewire:user.notif-badge />

                <!-- 🔹 Dropdown Profil -->
                <li class="relative" x-data="{ openDropdown: false }">
                    <button @click="openDropdown = !openDropdown" @click.away="openDropdown = false" class="flex items-center space-x-2 focus:outline-none p-1 rounded-full hover:bg-gray-100 transition">
                        <span class="text-sm font-medium text-gray-600 hidden sm:inline-block">
                            {{ Auth::user()->name ?? 'Pelanggan' }}
                        </span>
                        <div class="w-9 h-9 rounded-full bg-cyan-600 flex items-center justify-center text-white text-sm font-bold shadow-md border-2 border-white">
                            {{ mb_substr(Auth::user()->name ?? 'P', 0, 1) }}
                        </div>
                    </button>

                    <ul x-show="openDropdown" x-transition.opacity x-cloak
                        class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-xl shadow-xl z-50 overflow-hidden origin-top-right">
                        <li>
                            <a href="{{ route('profile') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-cyan-50 hover:text-cyan-700 transition">
                                Profil Saya
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('upload.identity') }}" class="block px-4 py-3 text-sm text-gray-700 hover:bg-cyan-50 hover:text-cyan-700 transition border-t border-gray-50">
                                Upload Identitas
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-50">
                                @csrf
                                <button type="submit" class="flex items-center w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H3" />
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            @endauth
        </ul>

        <!-- 🔹 Tombol Hamburger (Mobile) -->
        <button @click="open = !open" class="md:hidden text-cyan-600 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- 🔹 Menu Mobile -->
    <div x-show="open" @click.away="open = false" x-transition class="md:hidden bg-white border-t border-gray-200 shadow-inner" x-cloak>
        <ul class="flex flex-col text-center py-4 space-y-2 font-medium">
            <li><a href="{{ route('dashboard') }}" class="block py-2 text-gray-700 hover:text-cyan-600 hover:bg-gray-50">Dashboard</a></li>
            <li><a href="{{ route('mobils.index') }}" class="block py-2 text-gray-700 hover:text-cyan-600 hover:bg-gray-50">Armada</a></li>
            
            <li>
                <a href="{{ route('pesanan.saya') }}" class="block py-2 text-gray-700 hover:text-cyan-600 hover:bg-gray-50 flex items-center justify-center gap-2">
                    Pesanan Saya
                </a>
            </li>
            
            <li><a href="{{ route('profile') }}" class="block py-2 text-gray-700 hover:text-cyan-600 hover:bg-gray-50">Profil Saya</a></li>
            <li><a href="{{ route('upload.identity') }}" class="block py-2 text-gray-700 hover:text-cyan-600 hover:bg-gray-50">Upload Identitas</a></li>

            @auth
                <li class="flex justify-center items-center py-4 border-t border-gray-100 mt-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center space-x-2 text-red-600 bg-red-50 hover:bg-red-600 hover:text-white px-6 py-2 rounded-full transition font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H3" />
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </li>
            @else
                <li><a href="{{ route('login') }}" class="block py-3 mt-2 bg-cyan-600 text-white mx-6 rounded-lg hover:bg-cyan-700 transition">Login</a></li>
            @endauth
        </ul>
    </div>
</nav>