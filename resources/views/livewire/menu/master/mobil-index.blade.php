<div class="py-12">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #0891b2; }
        
        /* Animasi Toast */
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .toast-enter { animation: slideInRight 0.3s ease-out forwards; }
        .toast-leave { animation: fadeOut 0.3s ease-in forwards; }
    </style>

    <!-- TOAST NOTIFICATION COMPONENT (Alpine.js) -->
    <div x-data="{ toasts: [] }" 
         @show-toast.window="
            let id = Date.now();
            toasts.push({ id: id, type: $event.detail.type, message: $event.detail.message });
            setTimeout(() => { toasts = toasts.filter(t => t.id !== id) }, 4000);
         "
         class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 max-w-sm w-full pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="toast-enter pointer-events-auto flex items-center w-full p-4 rounded-xl shadow-lg border"
                 :class="{
                     'bg-white border-green-100': toast.type === 'success',
                     'bg-white border-red-100': toast.type === 'error'
                 }">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-10 h-10 rounded-lg"
                     :class="{
                         'bg-green-50 text-green-500': toast.type === 'success',
                         'bg-red-50 text-red-500': toast.type === 'error'
                     }">
                    <!-- Success Icon -->
                    <svg x-show="toast.type === 'success'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    <!-- Error Icon -->
                    <svg x-show="toast.type === 'error'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </div>
                <div class="ml-3 text-sm font-medium text-gray-800" x-text="toast.message"></div>
                <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 inline-flex h-8 w-8 text-gray-400 hover:text-gray-900 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
        </template>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

            <!-- Header & Action -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Armada</h2>
                
                {{-- TOMBOL CREATE HANYA UNTUK ADMIN --}}
                @can('create-mobils')
                <button wire:click="create"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Mobil
                </button>
                @endcan
            </div>

            <!-- Search & Filter -->
            <div class="mb-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari Plat Nomor, Merek, atau Tipe..."
                    class="pl-10 w-full md:w-1/3 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
            </div>

            <!-- Tabel Data -->
            <div class="overflow-x-auto rounded-lg shadow ring-1 ring-black ring-opacity-5">
                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mobil & Plat</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Spesifikasi</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Harga/Hari</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($mobils as $mobil)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($mobil->foto)
                                        <div class="flex-shrink-0 h-16 w-24">
                                            <img class="h-16 w-24 object-cover rounded-md shadow-sm border border-gray-200"
                                                src="{{ asset('storage/' . $mobil->foto) }}" alt="{{ $mobil->merek }}">
                                        </div>
                                    @else
                                        <div class="h-16 w-24 bg-gray-100 rounded-md flex items-center justify-center text-gray-400 text-xs border border-gray-200">
                                            No Image
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $mobil->merek }} {{ $mobil->tipe }}</div>
                                    <div class="text-xs font-mono bg-gray-100 px-2 py-0.5 rounded inline-block mt-1 text-gray-600 border border-gray-200">
                                        {{ $mobil->id }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-50 text-blue-800 border border-blue-100">
                                        {{ ucfirst($mobil->transmisi) }}
                                    </span>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-50 text-indigo-800 border border-indigo-100 ml-1">
                                        {{ $mobil->kursi }} Kursi
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1 capitalize">{{ $mobil->warna }}</div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">Rp {{ number_format($mobil->harga, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @php
                                        $statusClasses = match ($mobil->status) {
                                            'tersedia' => 'bg-green-100 text-green-800 border-green-200',
                                            'disewa' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'pemeliharaan' => 'bg-red-100 text-red-800 border-red-200',
                                            'dibersihkan' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            default => 'bg-gray-100 text-gray-800 border-gray-200',
                                        };
                                    @endphp
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full border {{ $statusClasses }} uppercase tracking-wide">
                                        {{ $mobil->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap text-sm font-medium">
                                    <div class="flex justify-center space-x-3">
                                        
                                        {{-- TOMBOL UBAH STATUS SAJA (Bisa Diakses Staff) --}}
                                        <button wire:click="openStatusModal('{{ $mobil->id }}')"
                                            class="text-yellow-600 hover:text-yellow-900 transition transform hover:scale-110"
                                            title="Ubah Status Ketersediaan">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>

                                        {{-- TOMBOL EDIT FULL (Hanya Admin) --}}
                                        @can('update-mobils')
                                        <button wire:click="edit('{{ $mobil->id }}')"
                                            class="text-blue-600 hover:text-blue-900 transition transform hover:scale-110"
                                            title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        @endcan

                                        {{-- TOMBOL HAPUS (Hanya Admin) --}}
                                        @can('delete-mobils')
                                        <button wire:confirm="Yakin ingin menghapus mobil {{ $mobil->merek }} ini?"
                                            wire:click="delete('{{ $mobil->id }}')"
                                            class="text-red-600 hover:text-red-900 transition transform hover:scale-110"
                                            title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                        </svg>
                                        <p>Belum ada data armada.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $mobils->links('components.pagination-info') }}
            </div>
        </div>
    </div>

    <!-- MODERN MODAL FORM FULL EDIT (ADMIN ONLY) -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
            aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity backdrop-blur-sm"
                    aria-hidden="true" wire:click="$set('showModal', false)">
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">

                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-bold text-gray-800" id="modal-title">
                            {{ $isEditMode ? 'Edit Data Armada' : 'Tambah Armada Baru' }}
                        </h3>
                        <button wire:click="$set('showModal', false)"
                            class="text-gray-400 hover:text-gray-600 transition focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                        <div class="bg-white px-6 py-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                                <!-- 1. PLAT NOMOR (Khusus Plat Nomor kita pakai entangle.live agar reaktif) -->
                                <div class="sm:col-span-2" x-data="{
                                    plat_full: @entangle('plat_nomor').live,
                                    prefix: '',
                                    number: '',
                                    suffix: '',
                                    syncFromFull() {
                                        if (this.plat_full) {
                                            let parts = this.plat_full.split(' ');
                                            this.prefix = parts[0] || '';
                                            this.number = parts[1] || '';
                                            this.suffix = parts[2] || '';
                                        } else {
                                            this.prefix = '';
                                            this.number = '';
                                            this.suffix = '';
                                        }
                                    },
                                    syncToFull() {
                                        this.plat_full = (this.prefix.trim() + ' ' + this.number.trim() + ' ' + this.suffix.trim()).toUpperCase().trim();
                                    }
                                }" x-init="syncFromFull(); $watch('plat_full', value => syncFromFull())">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                                        Plat Nomor (ID Kendaraan) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-2">
                                        <div class="relative w-1/4">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-400 font-bold text-xs">🇮🇩</span>
                                            </div>
                                            <input x-model="prefix" @input="syncToFull()" type="text"
                                                placeholder="B"
                                                class="pl-8 uppercase w-full rounded-md shadow-sm transition duration-200 font-mono text-lg text-center
                                                @error('plat_nomor') border-red-500 focus:border-red-500 focus:ring-red-200 @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror"
                                                maxlength="2">
                                        </div>
                                        <div class="w-1/2">
                                            <input x-model="number" @input="syncToFull()" type="text"
                                                placeholder="1234"
                                                class="uppercase w-full rounded-md shadow-sm transition duration-200 font-mono text-lg text-center
                                                @error('plat_nomor') border-red-500 focus:border-red-500 focus:ring-red-200 @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror"
                                                maxlength="4">
                                        </div>
                                        <div class="w-1/4">
                                            <input x-model="suffix" @input="syncToFull()" type="text"
                                                placeholder="XYZ"
                                                class="uppercase w-full rounded-md shadow-sm transition duration-200 font-mono text-lg text-center
                                                @error('plat_nomor') border-red-500 focus:border-red-500 focus:ring-red-200 @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror"
                                                maxlength="3">
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Format: <span class="font-mono bg-gray-100 px-1 rounded uppercase">Huruf Depan, Nomor, Huruf Belakang</span></p>
                                    @error('plat_nomor')
                                        <span class="text-red-500 text-xs font-bold mt-1 block flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <!-- 2. Merek -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Merek Mobil <span class="text-red-500">*</span></label>
                                    <input wire:model.live.debounce.300ms="merek" type="text" placeholder="Toyota, Honda, dll"
                                        class="w-full rounded-md shadow-sm transition duration-200 
                                        @error('merek') border-red-500 focus:border-red-500 focus:ring-red-200 @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror">
                                    @error('merek') 
                                        <span class="text-red-500 text-xs mt-1 block font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- 3. Tipe -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe / Model <span class="text-red-500">*</span></label>
                                    <input wire:model.live.debounce.300ms="tipe" type="text" placeholder="Avanza, Civic, dll"
                                        class="w-full rounded-md shadow-sm transition duration-200
                                        @error('tipe') border-red-500 focus:border-red-500 focus:ring-red-200 @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror">
                                    @error('tipe') 
                                        <span class="text-red-500 text-xs mt-1 block font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- 4. Warna -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Warna <span class="text-red-500">*</span></label>
                                    <input wire:model.live.debounce.300ms="warna" type="text" placeholder="Hitam, Putih, dll"
                                        class="w-full rounded-md shadow-sm transition duration-200
                                        @error('warna') border-red-500 focus:border-red-500 focus:ring-red-200 @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror">
                                    @error('warna') 
                                        <span class="text-red-500 text-xs mt-1 block font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- 5. Harga -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Sewa / Hari (Rp) <span class="text-red-500">*</span></label>
                                    <div class="relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input wire:model.live.debounce.300ms="harga" type="number" placeholder="0"
                                            class="pl-10 w-full rounded-md shadow-sm transition duration-200 text-right font-mono
                                            @error('harga') border-red-500 focus:border-red-500 focus:ring-red-200 @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror">
                                    </div>
                                    @error('harga') 
                                        <span class="text-red-500 text-xs mt-1 block font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- 6. Transmisi -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Transmisi <span class="text-red-500">*</span></label>
                                    <select wire:model.live="transmisi" class="w-full rounded-md shadow-sm transition duration-200 bg-white
                                        @error('transmisi') border-red-500 focus:border-red-500 focus:ring-red-200 @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror">
                                        <option value="">-- Pilih Transmisi --</option>
                                        <option value="manual">Manual</option>
                                        <option value="otomatis">Otomatis</option>
                                    </select>
                                    @error('transmisi') 
                                        <span class="text-red-500 text-xs mt-1 block font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- 7. Kursi -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Kursi <span class="text-red-500">*</span></label>
                                    <select wire:model.live="kursi" class="w-full rounded-md shadow-sm transition duration-200 bg-white
                                        @error('kursi') border-red-500 focus:border-red-500 focus:ring-red-200 @else border-gray-300 focus:border-blue-500 focus:ring-blue-200 @enderror">
                                        <option value="">-- Pilih --</option>
                                        <option value="5">5 Kursi (City Car/Sedan)</option>
                                        <option value="7">7 Kursi (MPV)</option>
                                        <option value="9">9 Kursi (SUV)</option>
                                        <option value="14">14 Kursi (Travel)</option>
                                        <option value="19">19 Kursi (Minibus)</option>
                                    </select>
                                    @error('kursi') 
                                        <span class="text-red-500 text-xs mt-1 block font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- 8. Status -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Ketersediaan <span class="text-red-500">*</span></label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                        <label class="cursor-pointer">
                                            <input type="radio" wire:model.live="status" value="tersedia" class="peer sr-only">
                                            <div class="text-center rounded-md border py-2 px-3 hover:bg-gray-50 peer-checked:bg-green-100 peer-checked:text-green-700 peer-checked:border-green-500 transition @error('status') border-red-500 @else border-gray-200 @enderror">Tersedia</div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" wire:model.live="status" value="disewa" class="peer sr-only">
                                            <div class="text-center rounded-md border py-2 px-3 hover:bg-gray-50 peer-checked:bg-blue-100 peer-checked:text-blue-700 peer-checked:border-blue-500 transition @error('status') border-red-500 @else border-gray-200 @enderror">Disewa</div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" wire:model.live="status" value="pemeliharaan" class="peer sr-only">
                                            <div class="text-center rounded-md border py-2 px-3 hover:bg-gray-50 peer-checked:bg-red-100 peer-checked:text-red-700 peer-checked:border-red-500 transition @error('status') border-red-500 @else border-gray-200 @enderror">Service</div>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" wire:model.live="status" value="dibersihkan" class="peer sr-only">
                                            <div class="text-center rounded-md border py-2 px-3 hover:bg-gray-50 peer-checked:bg-yellow-100 peer-checked:text-yellow-700 peer-checked:border-yellow-500 transition @error('status') border-red-500 @else border-gray-200 @enderror">Cuci</div>
                                        </label>
                                    </div>
                                    @error('status') 
                                        <span class="text-red-500 text-xs mt-1 block font-medium flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- 9. Foto Upload -->
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Foto Mobil <span class="text-red-500">*</span></label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md transition bg-gray-50 
                                        @error('foto') border-red-500 hover:border-red-600 bg-red-50 @else border-gray-300 hover:border-blue-400 @enderror">
                                        <div class="space-y-1 text-center">
                                            @if ($foto)
                                                <img src="{{ $foto->temporaryUrl() }}" class="mx-auto h-48 object-cover rounded-lg shadow-md mb-3">
                                                <p class="text-xs text-green-600 font-semibold">Foto Baru Siap Disimpan</p>
                                            @elseif ($foto_lama)
                                                <img src="{{ asset('storage/' . $foto_lama) }}" class="mx-auto h-48 object-cover rounded-lg shadow-md mb-3">
                                                <p class="text-xs text-gray-500">Foto Saat Ini</p>
                                            @else
                                                <svg class="mx-auto h-12 w-12 @error('foto') text-red-400 @else text-gray-400 @enderror" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            @endif

                                            <div class="flex text-sm text-gray-600 justify-center">
                                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                    <span>Upload file gambar</span>
                                                    <input id="file-upload" wire:model.live="foto" type="file" accept=".jpg,.jpeg,.png" class="sr-only">
                                                </label>
                                                <p class="pl-1">atau drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">Hanya JPG, PNG (Maks 2MB)</p>
                                        </div>
                                    </div>
                                    <div wire:loading wire:target="foto" class="text-sm text-blue-500 mt-2 font-semibold text-center w-full animate-pulse">Sedang mengupload foto...</div>
                                    @error('foto') 
                                        <span class="text-red-500 text-xs mt-2 font-medium flex items-center justify-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-xl border-t border-gray-100">
                            <button type="submit" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="{{ $isEditMode ? 'update' : 'store' }}">{{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Data' }}</span>
                                <span wire:loading wire:target="{{ $isEditMode ? 'update' : 'store' }}">Menyimpan...</span>
                            </button>
                            <button wire:click="$set('showModal', false)" type="button" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL QUICK EDIT STATUS (BISA DIAKSES STAFF) -->
    @if ($showStatusModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity backdrop-blur-sm" aria-hidden="true" wire:click="$set('showStatusModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border border-gray-100">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-bold text-gray-800" id="modal-title">Ubah Status Ketersediaan</h3>
                        <button wire:click="$set('showStatusModal', false)" class="text-gray-400 hover:text-gray-600 transition focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="updateStatusOnly">
                        <div class="bg-white px-6 py-6 space-y-4">
                            <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm text-blue-800">Ubah status operasional kendaraan <strong>{{ $id_asli }}</strong>.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Pilih Status Baru:</label>
                                <div class="grid grid-cols-1 gap-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model.live="status_edit" value="tersedia" class="peer sr-only">
                                        <div class="rounded-md border py-3 px-4 hover:bg-gray-50 peer-checked:bg-green-50 peer-checked:text-green-800 peer-checked:border-green-500 transition font-medium @error('status_edit') border-red-500 @else border-gray-200 @enderror">✅ Tersedia (Siap Disewa)</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model.live="status_edit" value="pemeliharaan" class="peer sr-only">
                                        <div class="rounded-md border py-3 px-4 hover:bg-gray-50 peer-checked:bg-red-50 peer-checked:text-red-800 peer-checked:border-red-500 transition font-medium @error('status_edit') border-red-500 @else border-gray-200 @enderror">🔧 Pemeliharaan (Service)</div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" wire:model.live="status_edit" value="dibersihkan" class="peer sr-only">
                                        <div class="rounded-md border py-3 px-4 hover:bg-gray-50 peer-checked:bg-yellow-50 peer-checked:text-yellow-800 peer-checked:border-yellow-500 transition font-medium @error('status_edit') border-red-500 @else border-gray-200 @enderror">🧼 Dibersihkan (Cuci)</div>
                                    </label>
                                </div>
                                @error('status_edit') 
                                    <span class="text-red-500 text-xs mt-2 block font-medium flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $message }}
                                    </span> 
                                @enderror
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-xl border-t border-gray-100">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Update Status
                            </button>
                            <button wire:click="$set('showStatusModal', false)" type="button" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>