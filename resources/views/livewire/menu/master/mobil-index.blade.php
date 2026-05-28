<div class="p-8 space-y-10 bg-[#f9fafb] min-h-screen font-inter">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #0891b2; }

        /* Animasi Toast & Fade */
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
        .animate-fade-in-down { animation: fadeInDown 0.4s ease-out forwards; }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <!-- TOAST NOTIFICATION (Alpine.js) -->
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
                     'bg-white border-emerald-100': toast.type === 'success',
                     'bg-white border-rose-100': toast.type === 'error',
                     'bg-white border-amber-100': toast.type === 'warning'
                 }">
                <div class="inline-flex items-center justify-center flex-shrink-0 w-10 h-10 rounded-lg"
                     :class="{
                         'bg-emerald-50 text-emerald-500': toast.type === 'success',
                         'bg-rose-50 text-rose-500': toast.type === 'error',
                         'bg-amber-50 text-amber-500': toast.type === 'warning'
                     }">
                    <svg x-show="toast.type === 'success'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    <svg x-show="toast.type === 'error'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                    <svg x-show="toast.type === 'warning'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                </div>
                <div class="ml-3 text-sm font-bold text-gray-800 tracking-tight" x-text="toast.message"></div>
            </div>
        </template>
    </div>

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-gray-200 pb-8">
        <div class="flex items-center gap-5">
            <div class="h-14 w-14 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-cyan-100 ring-4 ring-cyan-50">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Manajemen Armada</h1>
                <p class="text-sm text-gray-500 font-medium mt-2">Daftar kendaraan, spesifikasi, dan ketersediaan operasional.</p>
            </div>
        </div>
        
        @can('create-mobil')
        <button wire:click="create" class="bg-cyan-600 hover:bg-cyan-700 text-white font-black py-4 px-10 rounded-2xl shadow-xl shadow-cyan-100 flex items-center transition-all transform hover:scale-105 uppercase tracking-[0.2em] text-[11px] leading-none">
            <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Tambah Armada
        </button>
        @endcan
    </div>

    {{-- 2. CONTROL BAR (SEARCH) --}}
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex flex-col lg:flex-row gap-5 items-center">
        <div class="relative flex-1 w-full group">
            <span class="absolute inset-y-0 left-0 flex items-center pl-5 group-focus-within:text-cyan-600 transition-colors">
                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input wire:model.live.debounce.300ms="search" type="text" 
                class="w-full h-14 pl-14 pr-6 rounded-2xl border border-gray-100 bg-gray-50/50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-sm transition-all focus:outline-none placeholder:text-gray-400 placeholder:font-medium" 
                placeholder="Cari Plat Nomor, Merek, atau Tipe Mobil...">
        </div>
    </div>

    {{-- 3. DATA TABLE --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mt-6">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] ">Foto</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] ">Mobil & Plat</th>
                        <th class="px-8 py-5 text-center text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] ">Spesifikasi</th>
                        <th class="px-8 py-5 text-center text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] ">Harga/Hari</th>
                        <th class="px-8 py-5 text-center text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] ">Status</th>
                        <th class="px-8 py-5 text-right text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em] ">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($mobils as $mobil)
                    <tr class="hover:bg-cyan-50/20 transition-colors group">
                        <td class="px-8 py-6 whitespace-nowrap">
                            @if ($mobil->foto)
                                <div class="flex-shrink-0 h-16 w-24">
                                    <img class="h-16 w-24 object-cover rounded-xl shadow-sm border border-gray-200"
                                        src="{{ asset('storage/' . $mobil->foto) }}" alt="{{ $mobil->merek }}">
                                </div>
                            @else
                                <div class="h-16 w-24 bg-gray-50 rounded-xl flex items-center justify-center text-gray-400 font-bold uppercase tracking-widest text-[9px] border border-dashed border-gray-200">
                                    No Image
                                </div>
                            @endif
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex items-center gap-2 mb-1.5">
                                <div class="text-[15px] font-black text-gray-900 uppercase leading-none">{{ $mobil->merek }} {{ $mobil->tipe }}</div>
                                @if($mobil->status_kepemilikan === 'mitra')
                                    <span class="bg-indigo-100 text-indigo-700 border border-indigo-200 text-[8px] font-black px-2 py-0.5 rounded uppercase tracking-widest" title="Mobil Titipan/Mitra">Mitra</span>
                                @endif
                            </div>
                            <div class="text-xs font-mono font-bold bg-gray-100 px-2 py-0.5 rounded-lg inline-block text-cyan-700 tracking-widest">
                                {{ $mobil->id }}
                            </div>
                        </td>
                        <td class="px-8 py-6 text-center whitespace-nowrap">
                            <span class="px-3 py-1.5 inline-flex text-[10px] leading-none font-black rounded-lg bg-cyan-50 text-cyan-700 border border-cyan-100 uppercase tracking-widest">
                                {{ $mobil->transmisi }}
                            </span>
                            <span class="px-3 py-1.5 inline-flex text-[10px] leading-none font-black rounded-lg bg-gray-100 text-gray-700 border border-gray-200 ml-1 uppercase tracking-widest">
                                {{ $mobil->kursi }} Seat
                            </span>
                            <div class="text-[11px] font-bold text-gray-500 mt-2 uppercase tracking-widest">{{ $mobil->warna }}</div>
                        </td>
                        <td class="px-8 py-6 text-center whitespace-nowrap">
                            <div class="text-sm font-black text-gray-900">Rp {{ number_format($mobil->harga, 0, ',', '.') }}</div>
                            @if($mobil->status_kepemilikan === 'mitra')
                                <div class="text-[9px] text-gray-400 mt-1 uppercase font-bold tracking-widest">Bagi Hasil: {{ $mobil->persentase_bagi_hasil_rental }}% (Rental)</div>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-center whitespace-nowrap">
                            @php
                                $statusClasses = match ($mobil->status) {
                                    'tersedia' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                    'disewa' => 'bg-blue-50 text-blue-600 border-blue-100',
                                    'pemeliharaan' => 'bg-rose-50 text-rose-600 border-rose-100',
                                    'dibersihkan' => 'bg-amber-50 text-amber-600 border-amber-100',
                                    default => 'bg-gray-50 text-gray-600 border-gray-100',
                                };
                            @endphp
                            <span class="px-4 py-2 inline-flex text-[10px] font-black rounded-full border {{ $statusClasses }} uppercase tracking-[0.1em]">
                                {{ $mobil->status }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-3">
                                
                                {{-- TOMBOL UBAH STATUS SAJA (Bisa Diakses Admin & Staff) --}}
                                @canany(['update-mobil', 'create-inspeksi_mobil', 'read-inspeksi_mobil'])
                                <button wire:click="openStatusModal('{{ $mobil->id }}')"
                                    class="p-2.5 text-amber-600 bg-amber-50 hover:bg-amber-600 hover:text-white rounded-xl transition-all border border-amber-100 shadow-sm"
                                    title="Ubah Status Ketersediaan">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </button>
                                @endcanany

                                {{-- TOMBOL EDIT FULL (Hanya Admin) --}}
                                @can('update-mobil')
                                <button wire:click="edit('{{ $mobil->id }}')"
                                    class="p-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all border border-cyan-100 shadow-sm"
                                    title="Edit Data">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                @endcan

                                {{-- TOMBOL HAPUS (Hanya Admin) --}}
                                @can('delete-mobil')
                                <button onclick="confirm('Yakin ingin menghapus mobil {{ $mobil->merek }} ini?') || event.stopImmediatePropagation()"
                                    wire:click="delete('{{ $mobil->id }}')"
                                    class="p-2.5 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100 shadow-sm"
                                    title="Hapus Armada">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-24 text-center text-gray-400 text-sm font-medium italic">
                            Belum ada data armada terdaftar di dalam sistem.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
            {{ $mobils->links('components.pagination-info') }}
        </div>
    </div>

    <!-- MODERN MODAL FORM FULL EDIT (ADMIN ONLY) -->
    @if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl w-full border border-gray-100">
                <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                    
                    <!-- HEADER MODAL FIXED -->
                    <div class="px-10 py-6 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">{{ $isEditMode ? 'Edit Data Armada' : 'Tambah Armada Baru' }}</h3>
                            <p class="text-[11px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-2 leading-none">Spesifikasi & Informasi Kendaraan</p>
                        </div>
                        <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- CONTENT SCROLLABLE -->
                    <div class="bg-white p-10 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <div class="space-y-8">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">

                                <!-- 1. PLAT NOMOR -->
                                <div class="sm:col-span-2 group" x-data="{
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
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                        Plat Nomor (ID Kendaraan) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="flex gap-3">
                                        <div class="relative w-1/4">
                                            <input x-model="prefix" @input="syncToFull()" type="text" placeholder="B"
                                                class="h-14 uppercase w-full rounded-2xl border border-gray-100 bg-gray-50/50 transition-all font-black text-lg text-center focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 focus:outline-none @error('plat_nomor') border-rose-500 @enderror"
                                                maxlength="2">
                                        </div>
                                        <div class="w-1/2">
                                            <input x-model="number" @input="syncToFull()" type="text" placeholder="1234"
                                                class="h-14 uppercase w-full rounded-2xl border border-gray-100 bg-gray-50/50 transition-all font-black text-lg text-center focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 focus:outline-none @error('plat_nomor') border-rose-500 @enderror"
                                                maxlength="4">
                                        </div>
                                        <div class="w-1/4">
                                            <input x-model="suffix" @input="syncToFull()" type="text" placeholder="XYZ"
                                                class="h-14 uppercase w-full rounded-2xl border border-gray-100 bg-gray-50/50 transition-all font-black text-lg text-center focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 focus:outline-none @error('plat_nomor') border-rose-500 @enderror"
                                                maxlength="3">
                                        </div>
                                    </div>
                                    <p class="text-[10px] font-bold text-gray-400 mt-2 ml-1 tracking-widest uppercase">Format: Huruf Depan, Nomor, Huruf Belakang</p>
                                    @error('plat_nomor')
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <!-- 2. Merek -->
                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                        Merek Mobil <span class="text-rose-500">*</span>
                                    </label>
                                    <input wire:model.live.debounce.300ms="merek" type="text" placeholder="Toyota, Honda, dll"
                                        class="w-full h-14 rounded-2xl px-6 font-bold transition-all focus:outline-none placeholder:text-gray-300
                                        @error('merek') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-gray-800 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                    @error('merek') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- 3. Tipe -->
                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                        Tipe / Model <span class="text-rose-500">*</span>
                                    </label>
                                    <input wire:model.live.debounce.300ms="tipe" type="text" placeholder="Avanza, Brio, dll"
                                        class="w-full h-14 rounded-2xl px-6 font-bold transition-all focus:outline-none placeholder:text-gray-300
                                        @error('tipe') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-gray-800 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                    @error('tipe') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- 4. Warna -->
                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                        Warna <span class="text-rose-500">*</span>
                                    </label>
                                    <input wire:model.live.debounce.300ms="warna" type="text" placeholder="Hitam, Putih, dll"
                                        class="w-full h-14 rounded-2xl px-6 font-bold transition-all focus:outline-none placeholder:text-gray-300
                                        @error('warna') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-gray-800 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                    @error('warna') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- 5. Harga -->
                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                        Harga Sewa / Hari (Termasuk PPN) <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400 font-bold">Rp</div>
                                        <input wire:model.live.debounce.300ms="harga" type="number" placeholder="0"
                                            class="w-full h-14 rounded-2xl pl-12 pr-6 font-black text-lg transition-all focus:outline-none
                                            @error('harga') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-cyan-700 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                    </div>
                                    @error('harga') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- 6. Transmisi -->
                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                        Transmisi <span class="text-rose-500">*</span>
                                    </label>
                                    <select wire:model.live="transmisi" 
                                        class="w-full h-14 rounded-2xl px-6 font-bold uppercase tracking-widest transition-all cursor-pointer focus:outline-none text-xs
                                        @error('transmisi') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                        <option value="">Pilih Transmisi</option>
                                        <option value="manual">Manual</option>
                                        <option value="otomatis">Otomatis</option>
                                    </select>
                                    @error('transmisi') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- 7. Kursi -->
                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                        Jumlah Kursi <span class="text-rose-500">*</span>
                                    </label>
                                    <select wire:model.live="kursi" 
                                        class="w-full h-14 rounded-2xl px-6 font-bold uppercase tracking-widest transition-all cursor-pointer focus:outline-none text-xs
                                        @error('kursi') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                        <option value="">Pilih Jumlah Kursi</option>
                                        <option value="5">5 Kursi (City Car)</option>
                                        <option value="7">7 Kursi (MPV/SUV)</option>
                                        <option value="9">9 Kursi (Minibus)</option>
                                        <option value="14">14 Kursi (Travel)</option>
                                        <option value="19">19 Kursi (Bus)</option>
                                    </select>
                                    @error('kursi') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>

                                <!-- ======================================================= -->
                                <!-- 8. KEPEMILIKAN & BAGI HASIL (DENGAN KALKULASI REALTIME) -->
                                <!-- ======================================================= -->
                                <div class="sm:col-span-2 pt-6 pb-2" x-data="{
                                    kepemilikan: @entangle('status_kepemilikan').live,
                                    hargaSewa: @entangle('harga').live,
                                    persenRental: @entangle('persentase_bagi_hasil_rental').live,
                                    
                                    get pendapatanBersih() {
                                        let val = Number(this.hargaSewa) || 0;
                                        // Rumus Pajak Inklusi 11% (Harga Asli = Total / 1.11)
                                        return Math.round(val / 1.11);
                                    },
                                    get pajakPpn() {
                                        return (Number(this.hargaSewa) || 0) - this.pendapatanBersih;
                                    },
                                    get persenMitra() {
                                        let p = Number(this.persenRental) || 0;
                                        return (p > 100 ? 0 : 100 - p);
                                    },
                                    get nominalRental() {
                                        let p = Number(this.persenRental) || 0;
                                        return Math.round(this.pendapatanBersih * (p / 100));
                                    },
                                    get nominalMitra() {
                                        return Math.round(this.pendapatanBersih * (this.persenMitra / 100));
                                    }
                                }">
                                    
                                    <div class="space-y-4">
                                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 text-center">Status Kepemilikan <span class="text-rose-500">*</span></label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <label class="flex items-center justify-center h-14 rounded-2xl border-2 cursor-pointer transition-all shadow-sm" :class="kepemilikan === 'milik_sendiri' ? 'border-cyan-500 bg-cyan-50/50 text-cyan-700 ring-4 ring-cyan-500/10' : 'border-gray-100 hover:border-cyan-200'">
                                                <input type="radio" wire:model.live="status_kepemilikan" value="milik_sendiri" class="hidden">
                                                <span class="text-[11px] font-black uppercase tracking-widest">Aset Pribadi</span>
                                            </label>
                                            <label class="flex items-center justify-center h-14 rounded-2xl border-2 cursor-pointer transition-all shadow-sm" :class="kepemilikan === 'mitra' ? 'border-indigo-500 bg-indigo-50/50 text-indigo-700 ring-4 ring-indigo-500/10' : 'border-gray-100 hover:border-indigo-200'">
                                                <input type="radio" wire:model.live="status_kepemilikan" value="mitra" class="hidden">
                                                <span class="text-[11px] font-black uppercase tracking-widest">Mobil Mitra</span>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- KOLOM TAMBAHAN UNTUK MITRA -->
                                    <div x-show="kepemilikan === 'mitra'" x-transition class="mt-8 space-y-6 animate-fade-in-down border-t border-dashed border-gray-200 pt-6">
                                        
                                        <div class="group">
                                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-indigo-600 transition-colors">
                                                Nama Pemilik Kendaraan <span class="text-rose-500">*</span>
                                            </label>
                                            <input wire:model.defer="nama_pemilik" type="text" placeholder="Bapak Budi..."
                                                class="w-full h-14 rounded-2xl px-6 font-bold transition-all focus:outline-none placeholder:text-gray-300
                                                @error('nama_pemilik') border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-gray-800 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 @enderror">
                                            @error('nama_pemilik') 
                                                <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">{{ $message }}</span> 
                                            @enderror
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <!-- Persentase Rental -->
                                            <div class="group">
                                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                                    Bagi Hasil Rental (%) <span class="text-rose-500">*</span>
                                                </label>
                                                <div class="relative">
                                                    <input wire:model.live.debounce.100ms="persentase_bagi_hasil_rental" type="number" min="0" max="100" placeholder="10"
                                                        class="w-full h-14 rounded-2xl px-6 pr-12 font-black text-xl transition-all focus:outline-none
                                                        @error('persentase_bagi_hasil_rental') border-rose-500 bg-rose-50 text-rose-900 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-cyan-700 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                                    <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-gray-400 font-bold">%</div>
                                                </div>
                                                @error('persentase_bagi_hasil_rental') 
                                                    <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 block tracking-widest">{{ $message }}</span> 
                                                @enderror
                                            </div>

                                            <!-- Persentase Mitra (Auto Calculated) -->
                                            <div class="group">
                                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1">
                                                    Bagi Hasil Mitra (%)
                                                </label>
                                                <div class="relative">
                                                    <input x-model="persenMitra" type="number" readonly disabled
                                                        class="w-full h-14 rounded-2xl px-6 pr-12 font-black text-xl transition-all border border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed">
                                                    <div class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-gray-400 font-bold">%</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- UI SIMULASI REALTIME -->
                                        <div class="bg-indigo-50 rounded-2xl p-6 border border-indigo-100">
                                            <h4 class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-4 text-center border-b border-indigo-100/50 pb-2">Simulasi Bagi Hasil (Per Hari)</h4>
                                            
                                            <div class="space-y-3">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs font-bold text-gray-500">Harga Sewa (Input)</span>
                                                    <span class="text-sm font-black text-gray-800">Rp <span x-text="(Number(hargaSewa) || 0).toLocaleString('id-ID')"></span></span>
                                                </div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs font-bold text-red-400">Pajak PPN (11%)</span>
                                                    <span class="text-sm font-black text-red-500">- Rp <span x-text="pajakPpn.toLocaleString('id-ID')"></span></span>
                                                </div>
                                                <div class="flex justify-between items-center pt-2 border-t border-dashed border-indigo-200">
                                                    <span class="text-xs font-black text-indigo-700">Pendapatan Bersih (Net)</span>
                                                    <span class="text-sm font-black text-indigo-700">Rp <span x-text="pendapatanBersih.toLocaleString('id-ID')"></span></span>
                                                </div>
                                            </div>

                                            <div class="mt-4 pt-4 border-t border-indigo-200 grid grid-cols-2 gap-4">
                                                <div class="bg-white p-3 rounded-xl shadow-sm text-center">
                                                    <span class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Hak Rental</span>
                                                    <span class="text-sm font-black text-cyan-600">Rp <span x-text="nominalRental.toLocaleString('id-ID')"></span></span>
                                                </div>
                                                <div class="bg-white p-3 rounded-xl shadow-sm text-center">
                                                    <span class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Hak Mitra</span>
                                                    <span class="text-sm font-black text-emerald-600">Rp <span x-text="nominalMitra.toLocaleString('id-ID')"></span></span>
                                                </div>
                                            </div>
                                            <p class="text-[9px] text-center text-indigo-300 font-bold mt-4 italic">*Pajak PPN otomatis dipotong sebelum dilakukan pembagian hasil.</p>
                                        </div>

                                    </div>
                                </div>


                                <!-- 9. Status Ketersediaan -->
                                <div class="sm:col-span-2 space-y-4">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 text-center">Status Ketersediaan Armada <span class="text-rose-500">*</span></label>
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                        <label class="flex items-center justify-center h-14 rounded-2xl border-2 cursor-pointer transition-all shadow-sm {{ $status === 'tersedia' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-700 ring-4 ring-emerald-500/10' : 'border-gray-100 hover:border-emerald-200' }}">
                                            <input type="radio" wire:model.live="status" value="tersedia" class="hidden">
                                            <span class="text-[10px] font-black uppercase tracking-widest">Tersedia</span>
                                        </label>
                                        <label class="flex items-center justify-center h-14 rounded-2xl border-2 cursor-pointer transition-all shadow-sm {{ $status === 'disewa' ? 'border-blue-500 bg-blue-50/50 text-blue-700 ring-4 ring-blue-500/10' : 'border-gray-100 hover:border-blue-200' }}">
                                            <input type="radio" wire:model.live="status" value="disewa" class="hidden">
                                            <span class="text-[10px] font-black uppercase tracking-widest">Disewa</span>
                                        </label>
                                        <label class="flex items-center justify-center h-14 rounded-2xl border-2 cursor-pointer transition-all shadow-sm {{ $status === 'pemeliharaan' ? 'border-rose-500 bg-rose-50/50 text-rose-700 ring-4 ring-rose-500/10' : 'border-gray-100 hover:border-rose-200' }}">
                                            <input type="radio" wire:model.live="status" value="pemeliharaan" class="hidden">
                                            <span class="text-[10px] font-black uppercase tracking-widest">Service</span>
                                        </label>
                                        <label class="flex items-center justify-center h-14 rounded-2xl border-2 cursor-pointer transition-all shadow-sm {{ $status === 'dibersihkan' ? 'border-amber-500 bg-amber-50/50 text-amber-700 ring-4 ring-amber-500/10' : 'border-gray-100 hover:border-amber-200' }}">
                                            <input type="radio" wire:model.live="status" value="dibersihkan" class="hidden">
                                            <span class="text-[10px] font-black uppercase tracking-widest">Cuci</span>
                                        </label>
                                    </div>
                                    @error('status') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-2 block tracking-widest text-center">{{ $message }}</span> 
                                    @enderror
                                </div>

                                <!-- 10. Foto Upload -->
                                <div class="sm:col-span-2 group pt-4 border-t border-dashed border-gray-200">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 text-center">Foto Unit Kendaraan <span class="text-rose-500">*</span></label>
                                    <label class="relative flex flex-col items-center justify-center w-full h-56 border-2 border-dashed rounded-3xl cursor-pointer transition-all bg-gray-50/30 overflow-hidden
                                        @error('foto') border-rose-400 hover:border-rose-500 bg-rose-50/30 @else border-gray-200 hover:bg-gray-50 hover:border-cyan-300 group-hover:border-cyan-400 @enderror">
                                        
                                        @if ($foto)
                                            <img src="{{ $foto->temporaryUrl() }}" class="h-full w-full object-cover opacity-90">
                                            <div class="absolute inset-0 flex items-center justify-center bg-gray-900/50 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <span class="text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1 border border-white rounded-full">Ganti Foto Baru</span>
                                            </div>
                                        @elseif ($foto_lama)
                                            <img src="{{ asset('storage/' . $foto_lama) }}" class="h-full w-full object-cover opacity-90">
                                            <div class="absolute inset-0 flex items-center justify-center bg-gray-900/50 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <span class="text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1 border border-white rounded-full">Ganti Foto Baru</span>
                                            </div>
                                        @else
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg class="w-10 h-10 mb-4 transition-colors @error('foto') text-rose-400 @else text-gray-300 group-hover:text-cyan-500 @enderror" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                <p class="text-[11px] font-bold uppercase tracking-widest @error('foto') text-rose-500 @else text-gray-400 group-hover:text-cyan-600 @enderror">Klik untuk Upload Foto Armada</p>
                                                <p class="text-[9px] font-medium text-gray-400 mt-2">Hanya JPG, PNG (Maksimal 2MB)</p>
                                            </div>
                                        @endif

                                        <input type="file" wire:model.live="foto" class="hidden" accept="image/*">
                                    </label>
                                    <div wire:loading wire:target="foto" class="text-[10px] text-cyan-500 font-bold mt-3 text-center block animate-pulse uppercase tracking-widest">Sedang mengunggah foto...</div>
                                    @error('foto') 
                                        <span class="text-rose-500 text-[10px] font-black uppercase mt-3 flex items-center justify-center gap-1 tracking-widest">
                                            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $message }}
                                        </span> 
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                        <button type="submit" wire:loading.attr="disabled"
                            class="relative inline-flex justify-center items-center rounded-2xl px-12 py-4 bg-cyan-600 text-xs font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em] leading-none disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="{{ $isEditMode ? 'update' : 'store' }}">{{ $isEditMode ? 'Simpan Perubahan' : 'Simpan Data' }}</span>
                            <span wire:loading wire:target="{{ $isEditMode ? 'update' : 'store' }}" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                        <button type="button" wire:click="closeModal" 
                            class="inline-flex justify-center rounded-2xl px-8 py-4 bg-white text-xs font-black text-gray-400 hover:bg-gray-100 border border-gray-200 transition-all uppercase tracking-[0.2em] leading-none">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL QUICK EDIT STATUS (BISA DIAKSES STAFF & ADMIN) -->
    @if ($showStatusModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="$set('showStatusModal', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border border-gray-100">
                
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Ketersediaan</h3>
                        <p class="text-[10px] text-amber-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Ubah Status Cepat</p>
                    </div>
                    <button type="button" wire:click="$set('showStatusModal', false)" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form wire:submit.prevent="updateStatusOnly">
                    <div class="bg-white p-8 space-y-6">
                        <div class="bg-blue-50 border border-blue-100 p-4 rounded-2xl flex items-start gap-3 shadow-sm">
                            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-[11px] font-bold text-blue-800 leading-relaxed uppercase tracking-widest">Update status operasional armada plat <span class="font-black underline">{{ $id_asli }}</span></p>
                        </div>

                        <div>
                            <div class="grid grid-cols-1 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="status_edit" value="tersedia" class="peer sr-only">
                                    <div class="flex items-center h-14 rounded-2xl border-2 px-5 hover:bg-gray-50 peer-checked:bg-emerald-50 peer-checked:text-emerald-800 peer-checked:border-emerald-500 transition font-black text-xs tracking-widest uppercase @error('status_edit') border-rose-500 @else border-gray-100 @enderror">✅ Tersedia (Siap Disewa)</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="status_edit" value="pemeliharaan" class="peer sr-only">
                                    <div class="flex items-center h-14 rounded-2xl border-2 px-5 hover:bg-gray-50 peer-checked:bg-rose-50 peer-checked:text-rose-800 peer-checked:border-rose-500 transition font-black text-xs tracking-widest uppercase @error('status_edit') border-rose-500 @else border-gray-100 @enderror">🔧 Pemeliharaan (Service)</div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="status_edit" value="dibersihkan" class="peer sr-only">
                                    <div class="flex items-center h-14 rounded-2xl border-2 px-5 hover:bg-gray-50 peer-checked:bg-amber-50 peer-checked:text-amber-800 peer-checked:border-amber-500 transition font-black text-xs tracking-widest uppercase @error('status_edit') border-rose-500 @else border-gray-100 @enderror">🧼 Dibersihkan (Cuci)</div>
                                </label>
                            </div>
                            @error('status_edit') 
                                <span class="text-rose-500 text-[10px] font-black uppercase mt-3 flex items-center gap-1 tracking-widest">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $message }}
                                </span> 
                            @enderror
                        </div>
                    </div>

                    <div class="bg-gray-50/80 px-8 py-6 flex flex-row-reverse gap-3 rounded-b-[2.5rem] border-t border-gray-100">
                        <button type="submit" class="w-full inline-flex justify-center rounded-2xl py-4 bg-cyan-600 text-[11px] font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-widest">
                            Update Status
                        </button>
                        <button wire:click="$set('showStatusModal', false)" type="button" class="w-full inline-flex justify-center rounded-2xl py-4 bg-white text-[11px] font-black text-gray-400 hover:bg-gray-100 border border-gray-200 transition-all uppercase tracking-widest">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>