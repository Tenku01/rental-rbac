<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Aka Rental</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logoakarentcar.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logoakarentcar.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

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

    <!-- CSS Native untuk Animasi -->
    <style>
        .reveal {
            opacity: 0;
            transform: translateY(30px) translateZ(0);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
            will-change: opacity, transform;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0) translateZ(0);
        }

        .delay-100 {
            transition-delay: 100ms;
        }

        .delay-200 {
            transition-delay: 200ms;
        }

        .delay-300 {
            transition-delay: 300ms;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="antialiased relative bg-cover bg-center bg-no-repeat"
    style="background-image: url('{{ asset('images/bg3.jpg') }}');">

    <!-- Overlay solid putih dengan transparansi standar -->
    <div class="bg-white/75 min-h-screen">

        <!-- Navbar -->
        <nav x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)"
            :class="scrolled ? 'bg-white shadow-md py-2' : 'bg-transparent py-4'"
            class="fixed w-full top-0 left-0 z-50 transition-colors duration-300 ease-in-out">
            <div class="max-w-7xl mx-auto px-6 flex justify-between items-center transition-all duration-300">
                <!-- Logo -->
                <a href="#beranda"
                    class="flex items-center space-x-2 transform-gpu hover:scale-105 transition-transform duration-300">
                    <img src="/logoakarentcar.png" alt="Aka Rent Car" class="h-12 w-auto object-contain" />
                    <span class="sr-only">Aka Rent Car</span>
                </a>

                <!-- Menu Desktop -->
                <ul class="hidden md:flex space-x-8 text-sm font-bold text-gray-800 items-center">
                    <li><a href="#beranda" class="hover:text-cyan-600 transition-colors duration-200">Beranda</a></li>
                    <li><a href="#daftar-mobil" class="hover:text-cyan-600 transition-colors duration-200">Armada</a>
                    </li>
                    <li><a href="#tentang" class="hover:text-cyan-600 transition-colors duration-200">Tentang Kami</a>
                    </li>
                    <li><a href="#faq" class="hover:text-cyan-600 transition-colors duration-200">FAQ</a></li>
                    <li>
                        <a href="{{ route('login') }}"
                            class="text-cyan-600 border-2 border-cyan-600 px-6 py-2 rounded-full hover:bg-cyan-600 hover:text-white hover:shadow-lg hover:shadow-cyan-500/40 transform-gpu hover:-translate-y-0.5 transition-all duration-300">
                            Login
                        </a>
                    </li>
                </ul>

                <!-- Tombol Hamburger -->
                <button @click="open = !open"
                    class="md:hidden text-cyan-600 focus:outline-none transform-gpu hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Menu Mobile -->
            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
                @click.away="open = false"
                class="md:hidden bg-white border-t border-gray-100 absolute w-full shadow-xl rounded-b-2xl">
                <ul class="flex flex-col text-center py-4 space-y-2 font-semibold text-gray-700">
                    <li><a href="#beranda" @click="open = false"
                            class="block py-3 hover:text-cyan-600 hover:bg-cyan-50 transition-colors">Beranda</a></li>
                    <li><a href="#daftar-mobil" @click="open = false"
                            class="block py-3 hover:text-cyan-600 hover:bg-cyan-50 transition-colors">Armada</a></li>
                    <li><a href="#tentang" @click="open = false"
                            class="block py-3 hover:text-cyan-600 hover:bg-cyan-50 transition-colors">Tentang Kami</a>
                    </li>
                    <li><a href="#faq" @click="open = false"
                            class="block py-3 hover:text-cyan-600 hover:bg-cyan-50 transition-colors">FAQ</a></li>
                    @auth
                        <li><a href="{{ url('/dashboard') }}"
                                class="block py-3 text-cyan-600 font-bold hover:bg-cyan-50 transition-colors">Dashboard</a>
                        </li>
                    @else
                        <li class="pt-2 pb-4">
                            <a href="{{ route('login') }}"
                                class="block mx-10 py-3 border-2 border-cyan-600 text-cyan-600 rounded-xl hover:bg-cyan-600 hover:text-white shadow-md transition-colors">
                                Login
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </nav>

        <!-- 🔹 SECTION 1: HERO -->
        <div id="beranda" class="relative isolate px-6 pt-14 lg:px-8 min-h-screen flex items-center overflow-hidden">
            <div aria-hidden="true"
                class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-cyan-100/40 via-transparent to-transparent">
            </div>

            <div class="mx-auto max-w-3xl py-32 sm:py-48 lg:py-56 text-center reveal">
                <h1 class="text-5xl font-extrabold tracking-tight text-gray-900 sm:text-7xl drop-shadow-sm">
                    Sewa Mobil <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-600 to-blue-600">Tanpa
                        Ribet.</span>
                </h1>
                <p
                    class="mt-8 text-lg font-medium text-gray-700 sm:text-xl/8 max-w-2xl mx-auto leading-relaxed reveal delay-100">
                    Rental mobil cepat, aman, dan nyaman untuk semua kebutuhan Anda.
                    Booking mudah, mobil siap pakai.
                </p>
                <div class="mt-10 flex items-center justify-center gap-x-6 reveal delay-200">
                    <a href="{{ route('login') }}"
                        class="rounded-full bg-cyan-600 px-8 py-4 text-sm font-bold text-white shadow-lg shadow-cyan-600/30 hover:bg-cyan-500 transform-gpu hover:scale-105 transition-all duration-300">
                        PESAN SEKARANG
                    </a>
                </div>
            </div>
        </div>

        <!-- 🔹 SECTION 2: DAFTAR MOBIL -->
        <section id="daftar-mobil" class="bg-gray-50 py-24 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16 reveal">
                    <h2 class="text-4xl font-extrabold text-gray-900">Daftar Mobil Kami</h2>
                    <div class="h-1.5 w-24 bg-cyan-500 rounded-full mx-auto mt-4"></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                    @foreach ($mobils as $index => $mobil)
                        <div class="reveal delay-{{ ($index % 3) * 100 }} group bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transform-gpu hover:-translate-y-2 transition-all duration-300 cursor-pointer flex flex-col"
                            @auth
onclick="window.location='{{ url('/mobil/' . $mobil->id) }}'"
                             @else
                                 onclick="window.location='{{ route('login') }}'" @endauth>
                            <div class="relative overflow-hidden h-56 bg-gray-100">
                                <img src="{{ asset('storage/' . $mobil->foto) }}" alt="{{ $mobil->tipe }}"
                                    loading="lazy"
                                    class="w-full h-full object-cover transform-gpu group-hover:scale-105 transition-transform duration-500 ease-out">
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent">
                                </div>
                                <div class="absolute bottom-4 left-4 right-4 flex justify-between items-end">
                                    <div
                                        class="bg-white text-gray-900 text-sm font-bold px-4 py-2 rounded-xl shadow-sm">
                                        Rp {{ number_format($mobil->harga, 0, ',', '.') }} <span
                                            class="text-xs font-medium text-gray-500">/ hari</span>
                                    </div>
                                    <div
                                        class="bg-cyan-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">
                                        Siap Jalan
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 flex-1 flex flex-col">
                                <h3
                                    class="text-2xl font-bold text-gray-800 group-hover:text-cyan-600 transition-colors">
                                    {{ $mobil->tipe }}</h3>
                                <p class="text-sm font-medium text-cyan-600 mb-4">{{ $mobil->merek }}</p>

                                <div class="grid grid-cols-2 gap-y-3 gap-x-2 text-sm text-gray-600 mb-6 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="p-1.5 bg-gray-50 rounded-lg text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01">
                                                </path>
                                            </svg>
                                        </span>
                                        <span class="font-medium">{{ $mobil->warna }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="p-1.5 bg-gray-50 rounded-lg text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                                </path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </span>
                                        <span class="font-medium capitalize">{{ $mobil->transmisi }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="p-1.5 bg-gray-50 rounded-lg text-gray-400">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                                </path>
                                            </svg>
                                        </span>
                                        <span class="font-medium">{{ $mobil->kursi }} Kursi</span>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <div
                                        class="flex items-center justify-center w-full bg-cyan-50 text-cyan-700 border border-cyan-100 group-hover:bg-cyan-500 group-hover:text-white px-4 py-3 rounded-xl font-bold transition-colors duration-200">
                                        Pesan Sekarang <svg
                                            class="w-4 h-4 ml-2 transform-gpu group-hover:translate-x-1 transition-transform"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- 🔹 SECTION 3: TENTANG KAMI -->
        <section id="tentang" class="bg-white py-24 relative overflow-hidden">
            <div class="max-w-4xl mx-auto px-6 text-center relative z-10 reveal">
                <span class="text-cyan-600 font-bold tracking-wider text-sm uppercase">Siapa Kami</span>
                <h2 class="text-4xl font-extrabold mt-2 mb-6 text-gray-900">Tentang Aka Rental</h2>

                <div class="bg-white p-10 rounded-3xl shadow-lg shadow-gray-100 border border-gray-100">
                    <p class="text-gray-600 text-lg leading-relaxed">
                        Kami menyediakan layanan rental mobil dengan berbagai pilihan tipe dan harga kompetitif.
                        Dedikasi kami adalah memastikan setiap perjalanan Anda aman, nyaman, dan tak terlupakan.
                        Kepuasan pelanggan adalah <strong class="text-cyan-600">prioritas utama kami</strong>.
                    </p>
                </div>
            </div>
        </section>

        <!-- 🔹 SECTION 4: TESTIMONI -->
        <section id="testimoni" class="bg-gray-50 py-24 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-16 reveal">
                    <span class="text-cyan-600 font-bold tracking-wider text-sm uppercase">Kata Mereka</span>
                    <h2 class="text-4xl font-extrabold mt-2 text-gray-900">Apa Kata Pelanggan Kami?</h2>
                    <div class="flex items-center justify-center gap-2 mt-4">
                        <svg class="w-5 h-5 text-gray-500" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-600">Berdasarkan ulasan asli di Google Maps</span>
                    </div>
                    <div class="h-1.5 w-24 bg-cyan-500 rounded-full mx-auto mt-4"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Testimoni 1 -->
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 reveal delay-100">
                        <div class="flex items-center gap-1 text-yellow-400 mb-4">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                        </div>
                        <p class="text-gray-600 italic mb-6">"Sewa Xpander buat acara keluarga keluar kota. Mobilnya
                            bersih, mesin sehat, AC dingin banget. Adminnya juga cepet balesnya nggak bertele-tele. Puas
                            banget pokoknya."</p>
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-cyan-100 text-cyan-700 rounded-full flex items-center justify-center font-bold text-lg">
                                BS</div>
                            <div>
                                <h4 class="font-bold text-gray-900">Budi Santoso</h4>
                                <span
                                    class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md flex items-center gap-1 w-max mt-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg> Penyewa Terverifikasi
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Testimoni 2 -->
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 reveal delay-200">
                        <div class="flex items-center gap-1 text-yellow-400 mb-4">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                        </div>
                        <p class="text-gray-600 italic mb-6">"Saya sewa mobil plus sopir, driver-nya mas Sandi ramah
                            banget dan tau rute alternatif waktu macet. Sangat ngebantu liburan kami yang waktunya
                            mepet. Rekomen banget!"</p>
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-rose-100 text-rose-700 rounded-full flex items-center justify-center font-bold text-lg">
                                SA</div>
                            <div>
                                <h4 class="font-bold text-gray-900">Siti Aminah</h4>
                                <span
                                    class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md flex items-center gap-1 w-max mt-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg> Penyewa Terverifikasi
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Testimoni 3 -->
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 reveal delay-300">
                        <div class="flex items-center gap-1 text-yellow-400 mb-4">
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <!-- Bintang 4.5 -->
                            <div class="relative w-5 h-5 text-gray-300">
                                <svg class="w-5 h-5 fill-current absolute" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                    </path>
                                </svg>
                                <svg class="w-5 h-5 fill-current text-yellow-400 absolute overflow-hidden"
                                    style="width: 50%" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <p class="text-gray-600 italic mb-6">"Harga paling masuk akal di area ini. Kondisi Brio-nya
                            masih mulus banget kaya mobil baru. Awalnya sempet ragu pas mau DP, tapi ternyata emang
                            amanah perusahaannya."</p>
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center font-bold text-lg">
                                AP</div>
                            <div>
                                <h4 class="font-bold text-gray-900">Andi Pratama</h4>
                                <span
                                    class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-md flex items-center gap-1 w-max mt-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg> Penyewa Terverifikasi
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 🔹 SECTION 5: FAQ (Tanya Jawab) -->
        <section id="faq" class="bg-white py-24 relative overflow-hidden" x-data="{ selected: 1 }">
            <div class="max-w-3xl mx-auto px-6 reveal">
                <div class="text-center mb-12">
                    <span class="text-cyan-600 font-bold tracking-wider text-sm uppercase">FAQ</span>
                    <h2 class="text-4xl font-extrabold mt-2 text-gray-900">Pertanyaan yang Sering Diajukan</h2>
                </div>

                <div class="space-y-4">
                    <!-- Pertanyaan 1 -->
                    <div class="border border-gray-200 rounded-2xl overflow-hidden transition-all duration-300"
                        :class="selected == 1 ? 'border-cyan-500 shadow-md ring-1 ring-cyan-500' : 'hover:border-gray-300'">
                        <button @click="selected !== 1 ? selected = 1 : selected = null"
                            class="w-full text-left px-6 py-5 flex justify-between items-center focus:outline-none bg-white">
                            <span class="font-bold text-gray-900" :class="selected == 1 ? 'text-cyan-600' : ''">Apa
                                saja syarat untuk menyewa mobil lepas kunci?</span>
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                                :class="selected == 1 ? 'rotate-180 text-cyan-600' : ''" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="selected == 1" x-collapse>
                            <div class="px-6 pb-5 pt-1 text-gray-600 text-sm leading-relaxed border-t border-gray-100">
                                Syarat lepas kunci adalah identitas seperti KTP dan SIM.
                            </div>
                        </div>
                    </div>

                    <!-- Pertanyaan 2 -->
                    <div class="border border-gray-200 rounded-2xl overflow-hidden transition-all duration-300"
                        :class="selected == 2 ? 'border-cyan-500 shadow-md ring-1 ring-cyan-500' : 'hover:border-gray-300'">
                        <button @click="selected !== 2 ? selected = 2 : selected = null"
                            class="w-full text-left px-6 py-5 flex justify-between items-center focus:outline-none bg-white">
                            <span class="font-bold text-gray-900" :class="selected == 2 ? 'text-cyan-600' : ''">Apakah
                                harga sewa sudah termasuk BBM dan Tol?</span>
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                                :class="selected == 2 ? 'rotate-180 text-cyan-600' : ''" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="selected == 2" x-collapse>
                            <div class="px-6 pb-5 pt-1 text-gray-600 text-sm leading-relaxed border-t border-gray-100">
                                Tol dan BBM tidak termasuk dalam harga sewa.
                            </div>
                        </div>
                    </div>

                    <!-- Pertanyaan 3 -->
                    <div class="border border-gray-200 rounded-2xl overflow-hidden transition-all duration-300"
                        :class="selected == 3 ? 'border-cyan-500 shadow-md ring-1 ring-cyan-500' : 'hover:border-gray-300'">
                        <button @click="selected !== 3 ? selected = 3 : selected = null"
                            class="w-full text-left px-6 py-5 flex justify-between items-center focus:outline-none bg-white">
                            <span class="font-bold text-gray-900"
                                :class="selected == 3 ? 'text-cyan-600' : ''">Bagaimana sistem denda jika saya
                                terlambat mengembalikan mobil?</span>
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                                :class="selected == 3 ? 'rotate-180 text-cyan-600' : ''" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="selected == 3" x-collapse>
                            <div class="px-6 pb-5 pt-1 text-gray-600 text-sm leading-relaxed border-t border-gray-100">
                                Denda keterlambatan akan dikenakan 10% per jam dari harga sewa per hari. Sebagai contoh,
                                jika harga sewa per hari adalah Rp 100.000, maka untuk setiap jam keterlambatan akan
                                dikenakan 10% nya yaitu Rp 10.000.
                            </div>
                        </div>
                    </div>

                    <!-- Pertanyaan 4 -->
                    <div class="border border-gray-200 rounded-2xl overflow-hidden transition-all duration-300"
                        :class="selected == 4 ? 'border-cyan-500 shadow-md ring-1 ring-cyan-500' : 'hover:border-gray-300'">
                        <button @click="selected !== 4 ? selected = 4 : selected = null"
                            class="w-full text-left px-6 py-5 flex justify-between items-center focus:outline-none bg-white">
                            <span class="font-bold text-gray-900" :class="selected == 4 ? 'text-cyan-600' : ''">Apakah
                                bisa pesan (booking) mendadak di hari H?</span>
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-300"
                                :class="selected == 4 ? 'rotate-180 text-cyan-600' : ''" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="selected == 4" x-collapse>
                            <div class="px-6 pb-5 pt-1 text-gray-600 text-sm leading-relaxed border-t border-gray-100">
                                Sangat bisa, asalkan unit mobil masih berstatus "Tersedia". Namun, kami sangat
                                menyarankan untuk booking maksimal H-1 agar kami dapat menyiapkan kendaraan dengan lebih
                                optimal untuk Anda.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 🔹 FOOTER MODERN -->
        <footer class="bg-gray-900 border-t border-gray-800 text-gray-300">
            <div class="max-w-7xl mx-auto px-6 py-16">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-8 reveal">

                    <!-- Kolom 1: Branding -->
                    <div class="col-span-1 md:col-span-2">
                        <a href="#beranda" class="flex items-center space-x-2 mb-6">
                            <h2 class="text-2xl font-extrabold text-white">AKA<span class="text-cyan-500">RENT</span>
                            </h2>
                        </a>
                        <p class="text-sm text-gray-400 mb-6 leading-relaxed max-w-sm">
                            Solusi transportasi tepercaya Anda. Kami menyediakan layanan sewa kendaraan kualitas terbaik
                            dengan proses yang aman, cepat, dan transparan.
                        </p>
                        <!-- Social Media -->
                        <div class="flex space-x-4">
                            <a href="#"
                                class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-cyan-600 hover:text-white transition-colors">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path
                                        d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                                </svg>
                            </a>
                            <a href="#"
                                class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-cyan-600 hover:text-white transition-colors">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path
                                        d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Kolom 2: Tautan Singkat -->
                    <div class="col-span-1">
                        <h4 class="text-white font-bold mb-6">Navigasi</h4>
                        <ul class="space-y-3 text-sm text-gray-400">
                            <li><a href="#beranda" class="hover:text-cyan-400 transition-colors">Beranda</a></li>
                            <li><a href="#daftar-mobil" class="hover:text-cyan-400 transition-colors">Daftar
                                    Armada</a></li>
                            <li><a href="#tentang" class="hover:text-cyan-400 transition-colors">Tentang Kami</a></li>
                            <li><a href="#faq" class="hover:text-cyan-400 transition-colors">Bantuan & FAQ</a></li>
                        </ul>
                    </div>

                    <!-- Kolom 3: Kontak -->
                    <div class="col-span-1">
                        <h4 class="text-white font-bold mb-6">Hubungi Kami</h4>
                        <ul class="space-y-4 text-sm text-gray-400">
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-cyan-500 mt-0.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>Sapen, Jl. Bimasakti No.70, Demangan, Kec. Gondokusuman, Kota Yogyakarta, Daerah
                                    Istimewa Yogyakarta 55221</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-cyan-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <span>akarentalyk@gmail.com</span>
                            </li>
                            <li class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-cyan-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                    </path>
                                </svg>
                                <span>082184640107</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div
                    class="mt-16 pt-8 border-t border-gray-800 text-center flex flex-col md:flex-row justify-between items-center gap-4 reveal delay-100">
                    <p class="text-xs text-gray-500">&copy; {{ date('Y') }} Aka Rental Mobil. Semua Hak
                        Dilindungi.</p>
                    <div class="flex gap-4 text-xs text-gray-500">
                        <a href="#" class="hover:text-cyan-400">Syarat & Ketentuan</a>
                        <a href="#" class="hover:text-cyan-400">Kebijakan Privasi</a>
                    </div>
                </div>
            </div>
        </footer>

    </div>

    <!-- 🔹 FLOATING LIVECHAT UI (Tanpa Login & Terintegrasi Reverb) -->
    <div x-data="guestChat" class="fixed bottom-6 right-6 z-50 flex flex-col items-end">

        <!-- Chat Box Window -->
        <div x-show="chatOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-80 mb-4 overflow-hidden flex flex-col"
            style="display: none;">

            <!-- Header Chat -->
            <div class="bg-cyan-600 text-white p-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <!-- Icon Customer Service -->
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                                </path>
                            </svg>
                        </div>
                        <span
                            class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-cyan-600 rounded-full"></span>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm">CS Aka Rental</h4>
                        <p class="text-xs text-cyan-100">Online & Siap membantu</p>
                    </div>
                </div>
                <button @click="chatOpen = false" class="text-cyan-100 hover:text-white focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Body Chat -->
            <div id="chat-body" class="p-4 bg-gray-50 h-64 overflow-y-auto flex flex-col gap-3 custom-scrollbar">
                <div class="text-xs text-center text-gray-400 my-2">Hari ini</div>

                <template x-for="msg in messages" :key="msg.id || msg.waktu">
                    <div class="w-full flex flex-col">
                        <!-- Jika pengirim_id null (Berarti Guest / Anda) -->
                        <div x-show="msg.pengirim_id === null" class="flex justify-end gap-2 w-full mt-2">
                            <div
                                class="bg-cyan-600 text-white p-3 rounded-2xl rounded-tr-none shadow-sm text-sm break-words max-w-[85%]">
                                <span x-text="msg.isi_pesan"></span>
                                <div class="text-[10px] text-cyan-200 text-right mt-1" x-text="msg.waktu"></div>
                            </div>
                        </div>

                        <!-- Jika pengirim_id tidak null (Berarti Admin/CS) -->
                        <div x-show="msg.pengirim_id !== null" class="flex gap-2 w-full mt-2">
                            <div
                                class="w-8 h-8 rounded-full bg-cyan-100 flex-shrink-0 flex items-center justify-center text-cyan-700 font-bold text-xs">
                                CS</div>
                            <div
                                class="bg-white border border-gray-100 p-3 rounded-2xl rounded-tl-none shadow-sm text-sm text-gray-700 break-words max-w-[85%]">
                                <span x-text="msg.isi_pesan"></span>
                                <div class="text-[10px] text-gray-400 text-right mt-1" x-text="msg.waktu"></div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Pesan Selamat Datang (hanya jika riwayat kosong) -->
                <div x-show="messages.length === 0" class="flex gap-2 mt-2">
                    <div
                        class="w-8 h-8 rounded-full bg-cyan-100 flex-shrink-0 flex items-center justify-center text-cyan-700 font-bold text-xs">
                        CS</div>
                    <div
                        class="bg-white border border-gray-100 p-3 rounded-2xl rounded-tl-none shadow-sm text-sm text-gray-700">
                        Halo kak! 👋<br>Ada yang bisa kami bantu seputar sewa mobil hari ini?
                    </div>
                </div>
            </div>

            <!-- Footer / Input Area -->
            <div class="p-3 bg-white border-t border-gray-100">
                <form @submit.prevent="sendMessage" class="flex items-center gap-2">
                    <input type="text" x-model="newMessage" placeholder="Ketik pesan..." required
                        class="w-full text-sm rounded-xl border border-gray-200 focus:ring-cyan-500 focus:border-cyan-500 px-3 py-2.5 bg-gray-50 outline-none transition-all">
                    <button type="submit"
                        class="bg-cyan-600 hover:bg-cyan-700 text-white p-2.5 rounded-xl transition-colors shrink-0">
                        <svg class="w-5 h-5 transform rotate-90" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z">
                            </path>
                        </svg>
                    </button>
                </form>
                <p class="text-center text-[10px] text-gray-400 mt-2">Ngobrol langsung dengan CS. Anda tidak perlu
                    login.</p>
            </div>
        </div>

        <!-- Floating Button Trigger -->
        <button @click="chatOpen = !chatOpen"
            class="w-16 h-16 bg-cyan-600 hover:bg-cyan-500 rounded-full shadow-2xl flex items-center justify-center transform-gpu hover:scale-110 transition-all duration-300 focus:outline-none ring-4 ring-cyan-600/30">
            <svg x-show="!chatOpen" class="w-8 h-8 text-white" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                </path>
            </svg>
            <svg x-show="chatOpen" class="w-8 h-8 text-white" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                </path>
            </svg>
        </button>
    </div>

    <!-- Alpine.js & Plugins -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Script Alpine Component untuk Livechat Reverb -->
<script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('guestChat', () => ({
                chatOpen: false,
                sessionId: '',
                newMessage: '',
                messages: [],

                init() {
                    // 1. Dapatkan atau buat UUID (session) baru di browser
                    this.sessionId = localStorage.getItem('guest_session_id');
                    if (!this.sessionId) {
                        this.sessionId = 'guest_' + crypto.randomUUID();
                        localStorage.setItem('guest_session_id', this.sessionId);
                    }

                    // 2. Tarik riwayat pesan lama
                    this.fetchMessages();

                    // 3. Berlangganan ke channel Reverb
                    setTimeout(() => {
                        if (window.Echo) {
                            window.Echo.channel('guest-chat.' + this.sessionId)
                                .listen('.App\\Events\\GuestMessageEvent', (e) => {
                                    // Ekstraksi data pesan
                                    let msgData = e.pesan ? e.pesan : e;

                                    // --- LOGIKA ANTI DOUBLE ---
                                    // 1. Jika pengirim_id != null, berarti ini pesan dari ADMIN (Wajib tampilkan)
                                    // 2. Cek apakah ID pesan ini sudah ada di layar (Cegah duplikasi)
                                    const isExists = this.messages.some(m => m.id === msgData.id);

                                    if (msgData.pengirim_id !== null && !isExists) {
                                        this.messages.push({
                                            id: msgData.id,
                                            isi_pesan: msgData.isi_pesan,
                                            pengirim_id: msgData.pengirim_id,
                                            waktu: msgData.waktu
                                        });
                                        this.scrollToBottom();
                                    }
                                    // Note: Jika pengirim_id == null, kita abaikan karena itu pesan 
                                    // milik guest sendiri yang sudah muncul via Optimistic Update.
                                });
                        }
                    }, 1000);
                },

                fetchMessages() {
                    fetch('/guest-chat/' + this.sessionId)
                        .then(res => res.json())
                        .then(data => {
                            this.messages = data;
                            this.scrollToBottom();
                        })
                        .catch(err => console.error('Gagal mengambil pesan:', err));
                },

                async sendMessage() {
                    if (this.newMessage.trim() === '') return;

                    const msgInput = this.newMessage;
                    this.newMessage = '';

                    // Optimistic Update: Tampilkan langsung dengan ID sementara (Timestamp)
                    const tempId = Date.now();
                    this.messages.push({
                        id: tempId,
                        isi_pesan: msgInput,
                        pengirim_id: null,
                        waktu: new Date().toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        })
                    });
                    this.scrollToBottom();

                    try {
                        const response = await fetch('/guest-chat/send', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                // X-Socket-ID penting agar Reverb tahu ini pengirimnya
                                'X-Socket-ID': window.Echo ? window.Echo.socketId() : ''
                            },
                            body: JSON.stringify({
                                session_id: this.sessionId,
                                isi_pesan: msgInput
                            })
                        });

                        const result = await response.json();

                        // Update ID sementara dengan ID asli dari Database agar sinkron
                        if (result.status === 'success') {
                            const index = this.messages.findIndex(m => m.id === tempId);
                            if (index !== -1) {
                                this.messages[index].id = result.pesan.id;
                                // Opsional: update waktu dari server
                                this.messages[index].waktu = result.pesan.waktu;
                            }
                        }
                    } catch (err) {
                        console.error('Gagal mengirim pesan:', err);
                    }
                },

                scrollToBottom() {
                    setTimeout(() => {
                        const el = document.getElementById('chat-body');
                        if (el) el.scrollTop = el.scrollHeight;
                    }, 50);
                }
            }));
        });
    </script>

    <!-- JavaScript Native IntersectionObserver -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const observerOptions = {
                root: null,
                rootMargin: '0px 0px -50px 0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal').forEach((el) => {
                observer.observe(el);
            });
        });
    </script>
</body>

</html>
