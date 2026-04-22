<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    

    <!-- Title -->
    <title>{{ config('app.name', 'Akarental - Rental Mobil') }}</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('logoakarentcar.png') }}" type="image/png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

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
</head>

<body class="font-sans antialiased bg-gray-100">
    <!-- 🔹 Navbar -->
    <x-navbar />

    <div class="min-h-screen pt-[70px]">
        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-4 mt-10">
        <div class="max-w-7xl mx-auto text-center text-sm">
            &copy; {{ date('Y') }} Akarental. All rights reserved.
        </div>
    </footer>
</body>
</html>
