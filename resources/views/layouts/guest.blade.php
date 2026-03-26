<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Akarental - Rental Mobil') }}</title>
        
 <!-- Menambahkan Favicon di Tab Browser -->
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
    <body class="font-sans text-gray-100 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-300">
            
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-100 shadow-md overflow-hidden sm:rounded-lg">
                <!-- Logo Centered -->
                <div class="flex justify-center mb-4">
                    <a href="/" wire:navigate>
                        <x-application-logo class="w-32 h-32 fill-current text-gray-500" />
                    </a>
                </div>

                <!-- Slot content (form login) -->
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
