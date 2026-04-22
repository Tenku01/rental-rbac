<div class="p-8 space-y-10 bg-[#f9fafb] min-h-screen font-inter">
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
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Laporan Kerusakan</h1>
                <p class="text-sm text-gray-500 font-medium mt-2">Daftar temuan kerusakan armada dan rincian biaya perbaikan.</p>
            </div>
        </div>

        @can('create-vehicle_damage_reports')
        <button wire:click="create" class="bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-black py-4 px-10 rounded-2xl shadow-xl shadow-cyan-100 flex items-center transition-all transform hover:scale-105 uppercase tracking-[0.2em] text-[11px] leading-none">
            <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Tambah Laporan
        </button>
        @endcan
    </div>

    {{-- 2. CONTROL BAR --}}
    <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex flex-col lg:flex-row items-center justify-between gap-4">
        <div class="relative w-full group flex-1">
            <span class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-gray-300 group-focus-within:text-cyan-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input wire:model.live.debounce.300ms="search" type="text" 
                class="w-full h-14 pl-14 pr-6 rounded-2xl border border-gray-100 bg-gray-50/50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-sm transition-all focus:outline-none placeholder:text-gray-400 placeholder:font-medium" 
                placeholder="Cari berdasarkan Kode Laporan, Plat Mobil, atau Kode Kembali...">
        </div>
    </div>

    {{-- 3. DATA TABLE --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mt-6">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Laporan & Unit</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Ref. Pengembalian</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Deskripsi Kerusakan</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-center">Estimasi Biaya</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($reports as $item)
                    <tr class="hover:bg-cyan-50/20 transition-colors group">
                        <td class="px-8 py-6">
                            <div class="font-black text-gray-900 text-sm tracking-tight">{{ $item->kode_laporan }}</div>
                            <div class="text-[10px] font-mono text-cyan-600 font-bold mt-1 uppercase tracking-tighter">{{ $item->mobil_id }}</div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="px-3 py-1.5 bg-gray-100 text-gray-600 text-[10px] font-black rounded-lg border border-gray-200 uppercase tracking-tighter">{{ $item->pengembalian_kode ?? 'N/A' }}</span>
                        </td>
                        <td class="px-8 py-6">
                            <!-- 🔹 Diperbarui: damage_description -> deskripsi_kerusakan -->
                            <p class="text-xs text-gray-600 line-clamp-2 max-w-xs font-medium italic">"{{ $item->deskripsi_kerusakan }}"</p>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <!-- 🔹 Diperbarui: damage_cost -> biaya_kerusakan -->
                            <div class="text-[13px] font-black text-rose-600">Rp {{ number_format($item->biaya_kerusakan, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end gap-3">
                                <button wire:click="showDetail('{{ $item->kode_laporan }}')" class="p-2.5 text-slate-600 bg-slate-50 hover:bg-slate-600 hover:text-white rounded-xl transition-all border border-slate-100 shadow-sm" title="Detail Laporan">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>

                                @can('update-vehicle_damage_reports')
                                <button wire:click="edit('{{ $item->kode_laporan }}')" class="p-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all border border-cyan-100 shadow-sm" title="Koreksi Laporan">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                @endcan

                                @can('delete-vehicle_damage_reports')
                                <button onclick="confirm('Hapus laporan kerusakan ini secara permanen?') || event.stopImmediatePropagation()" wire:click="delete('{{ $item->kode_laporan }}')" class="p-2.5 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-24 text-center text-gray-400 text-sm font-medium italic uppercase tracking-widest">Tidak ditemukan laporan kerusakan armada.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
            {{ $reports->links('components.pagination-info') }}
        </div>
    </div>

    {{-- 4. MODAL CREATE / EDIT --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                    <div class="px-10 py-6 border-b border-gray-100 bg-white sticky top-0 z-10 flex justify-between items-center">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">{{ $isEditMode ? 'Koreksi Kerusakan' : 'Buat Laporan Baru' }}</h3>
                            <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">{{ $isEditMode ? 'ID Laporan: ' . $editingKode : 'Pencatatan Insiden Baru' }}</p>
                        </div>
                        <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-10 space-y-8 max-h-[75vh] overflow-y-auto custom-scrollbar bg-white">
                        
                        @if(!$isEditMode)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                    Pilih Armada Mobil <span class="text-rose-500">*</span>
                                </label>
                                <select wire:model.live="mobil_id" 
                                    class="w-full h-14 rounded-2xl px-6 font-bold text-xs uppercase tracking-widest cursor-pointer transition-all focus:outline-none
                                    @error('mobil_id') border-rose-500 bg-rose-50 text-rose-900 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-gray-800 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                    <option value="">Pilih Mobil yang Rusak</option>
                                    @foreach($mobils as $mobil)
                                        <option value="{{ $mobil->id }}">{{ $mobil->merek }} ({{ $mobil->id }})</option>
                                    @endforeach
                                </select>
                                @error('mobil_id') 
                                    <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $message }}
                                    </span> 
                                @enderror
                            </div>

                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                    Ref. Kode Pengembalian <span class="lowercase opacity-60 font-medium">(Opsional)</span>
                                </label>
                                <input wire:model.live.debounce.300ms="pengembalian_kode" type="text" 
                                    class="w-full h-14 rounded-2xl border border-gray-100 bg-gray-50/50 px-6 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold transition-all focus:outline-none placeholder:text-gray-300 uppercase"
                                    placeholder="Contoh: RET-12345">
                                @error('pengembalian_kode') 
                                    <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">{{ $message }}</span> 
                                @enderror
                            </div>
                        </div>
                        @endif

                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                Rincian Kerusakan Armada <span class="text-rose-500">*</span>
                            </label>
                            <!-- 🔹 Diperbarui: damage_description -> deskripsi_kerusakan -->
                            <textarea wire:model.live.debounce.300ms="deskripsi_kerusakan" 
                                class="w-full h-32 rounded-[2rem] p-6 font-bold text-sm transition-all focus:outline-none shadow-inner placeholder:font-medium placeholder:text-gray-400
                                @error('deskripsi_kerusakan') border-rose-500 bg-rose-50 text-rose-900 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-gray-700 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror" 
                                placeholder="Jelaskan bagian yang rusak secara mendetail..."></textarea>
                            @error('deskripsi_kerusakan') 
                                <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $message }}
                                </span> 
                            @enderror
                        </div>

                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                Nilai Ganti Rugi / Estimasi Biaya (Rp) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-gray-400 font-black">Rp</div>
                                <!-- 🔹 Diperbarui: damage_cost -> biaya_kerusakan -->
                                <input wire:model.live.debounce.300ms="biaya_kerusakan" type="number" min="0" step="1000"
                                    class="w-full h-16 pl-14 rounded-2xl px-5 font-black text-xl transition-all focus:outline-none shadow-inner placeholder:text-gray-300
                                    @error('biaya_kerusakan') border-rose-500 bg-rose-50 text-rose-900 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50/50 text-gray-800 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                            </div>
                            @error('biaya_kerusakan') 
                                <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $message }}
                                </span> 
                            @enderror
                        </div>
                    </div>

                    <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                        <button type="submit" wire:loading.attr="disabled"
                            class="relative inline-flex justify-center items-center rounded-2xl px-12 py-4 bg-cyan-600 text-xs font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em] leading-none disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="store, update">{{ $isEditMode ? 'Simpan Perubahan' : 'Buat Laporan Baru' }}</span>
                            <span wire:loading wire:target="store, update" class="flex items-center gap-2">
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

    {{-- 5. MODAL DETAIL --}}
    @if($showDetailModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                <div class="px-10 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Detail Kerusakan: {{ $selectedReport->kode_laporan }}</h3>
                        <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Laporan Audit Fisik Kendaraan</p>
                    </div>
                    <button wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Informasi Armada</p>
                            <p class="font-black text-gray-800 text-xs uppercase">{{ $selectedReport->mobil->merek ?? 'Mobil Dihapus' }}</p>
                            <p class="text-[10px] font-mono text-cyan-600 font-bold mt-1 tracking-wider">[{{ $selectedReport->mobil_id }}]</p>
                        </div>
                        <div class="bg-white p-5 rounded-2xl border border-gray-100 text-right shadow-sm">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Penyewa Terakhir</p>
                            <p class="font-black text-gray-800 text-xs uppercase">{{ $selectedReport->pengembalian->peminjaman->user->name ?? 'User Dihapus / Manual' }}</p>
                            <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">{{ $selectedReport->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h5 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100 pb-2">Deskripsi Kerusakan</h5>
                        <div class="p-6 bg-amber-50/50 rounded-[2rem] border border-amber-100 text-amber-900 font-bold text-xs leading-relaxed italic shadow-inner">
                            <!-- 🔹 Diperbarui: damage_description -> deskripsi_kerusakan -->
                            "{{ $selectedReport->deskripsi_kerusakan }}"
                        </div>
                    </div>

                    <div class="p-6 bg-rose-50 rounded-[2rem] border-2 border-rose-100 flex justify-between items-center shadow-sm">
                        <div>
                            <p class="text-[10px] font-black text-rose-500 uppercase tracking-[0.2em]">Total Biaya Ganti Rugi</p>
                        </div>
                        <!-- 🔹 Diperbarui: damage_cost -> biaya_kerusakan -->
                        <p class="text-2xl font-black text-rose-600">Rp {{ number_format($selectedReport->biaya_kerusakan, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="bg-gray-50/80 px-10 py-8 text-right border-t border-gray-100">
                    <button wire:click="closeModal" class="px-10 py-4 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-gray-800 transition-all active:scale-95 shadow-md">Tutup Detail</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>