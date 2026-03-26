<!DOCTYPE html>
<html lang="en" class="h-full">
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
        @viteReactRefresh
        @vite(['resources/js/app.js', 'resources/css/app.css'])
    @endif

    @livewireStyles
</head>

<body class="h-full bg-gray-100 font-sans antialiased">

<!-- ROOT -->
<div x-data="{ sidebarOpen: true }" class="h-screen flex overflow-hidden">

    <!-- SIDEBAR -->
    <livewire:sidebar.sidebar />

    <!-- MAIN AREA -->
    <div class="flex-1 flex flex-col min-h-0 overflow-hidden">

        <!-- HEADER -->
        @include('components.admin-headbar')

        <!-- CONTENT -->
        <main 
            wire:transition 
            class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden bg-gray-100 p-4 sm:p-6 custom-scrollbar"
        >
            {{ $slot }}  
        </main>

    </div>
</div>

@livewireScripts
</body>
</html>