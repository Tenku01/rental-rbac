<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | {{ config('app.name') }}</title>
    
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
        @vite(['resources/js/app.js', 'resources/css/app.css'])
    @endif

    <!-- WAJIB ADA: Style Livewire -->
    @livewireStyles
</head>

<body class="bg-gray-100 font-sans antialiased">

<div x-data="{ sidebarOpen: true }" class="min-h-screen flex">

{{-- SIDEBAR --}}
    <livewire:sidebar.sidebar />

    {{-- MAIN AREA --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- HEADER --}}
        <!-- Pastikan file components.admin-headbar ada -->
        @include('components.admin-headbar')

        {{-- MAIN CONTENT --}}
        <main wire:transition class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 sm:p-6">
           {{ $slot }}  
        </main>

    </div>
</div>

<!-- WAJIB ADA: Script Livewire -->
<!-- AlpineJS sudah otomatis dibundle di sini oleh Livewire 3 -->
@livewireScripts

</body>
</html>