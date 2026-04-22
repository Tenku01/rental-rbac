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
                 'bg-white border-rose-100': toast.type === 'error'
             }">
            <div class="inline-flex items-center justify-center flex-shrink-0 w-10 h-10 rounded-lg"
                 :class="{
                     'bg-emerald-50 text-emerald-500': toast.type === 'success',
                     'bg-rose-50 text-rose-500': toast.type === 'error'
                 }">
                <svg x-show="toast.type === 'success'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                <svg x-show="toast.type === 'error'" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
            </div>
            <div class="ml-3 text-sm font-bold text-gray-800 tracking-tight" x-text="toast.message"></div>
        </div>
    </template>
</div>

{{-- 1. HEADER --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-gray-200 pb-8">
    <div class="flex items-center gap-5">
        <div class="h-14 w-14 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-cyan-100 ring-4 ring-cyan-50">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Data Sopir</h1>
            <p class="text-sm text-gray-500 font-medium mt-2">Manajemen pengemudi, lisensi, dan ketersediaan.</p>
        </div>
    </div>

    @can('create-sopir')
    <button wire:click="create" class="bg-cyan-600 hover:bg-cyan-700 text-white font-black py-4 px-10 rounded-2xl shadow-xl shadow-cyan-100 flex items-center transition-all transform hover:scale-105 uppercase tracking-[0.2em] text-[11px] leading-none">
        <svg class="w-4 h-4 mr-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
        Tambah Sopir
    </button>
    @endcan
</div>

{{-- 2. CONTROL BAR --}}
<div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex flex-col lg:flex-row gap-5 items-center">
    <!-- Filter Tabs -->
    <div class="flex gap-2 w-full lg:w-auto overflow-x-auto custom-scrollbar pb-2 lg:pb-0">
        @foreach(['' => 'Semua', 'Tersedia' => 'Tersedia', 'Bekerja' => 'Jalan', 'Tidak Tersedia' => 'Off'] as $key => $label)
        <button wire:click="$set('filterStatus', '{{ $key }}')"
            class="px-6 h-14 rounded-2xl text-[11px] font-black uppercase tracking-widest transition-all whitespace-nowrap border
            {{ $filterStatus === $key 
                ? 'bg-cyan-50 text-cyan-600 border-cyan-100 shadow-inner' 
                : 'bg-white text-gray-400 border-gray-100 hover:border-cyan-100 hover:text-cyan-500' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <!-- Search -->
    <div class="relative flex-1 w-full group">
        <span class="absolute inset-y-0 left-0 flex items-center pl-5 group-focus-within:text-cyan-600 transition-colors">
            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <input wire:model.live.debounce.300ms="search" type="text" 
            class="w-full h-14 pl-14 pr-6 rounded-2xl border-gray-100 bg-gray-50/50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-sm transition-all placeholder:not-italic" 
            placeholder="Cari Nama atau No SIM...">
    </div>
</div>

{{-- 3. DATA TABLE --}}
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mt-6">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left">
            <thead class="bg-gray-50/80 border-b border-gray-100">
                <tr>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em]">Nama Lengkap</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em]">No. SIM A/B1</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em]">Akun Login</th>
                    <th class="px-8 py-5 text-center text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em]">Status</th>
                    <th class="px-8 py-5 text-right text-[11px] font-extrabold text-gray-600 uppercase tracking-[0.15em]">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($sopirs as $item)
                <tr class="hover:bg-cyan-50/20 transition-colors group">
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-4">
                            <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-cyan-600 to-cyan-700 flex items-center justify-center text-white font-black text-xs shadow-lg shadow-cyan-100">
                                {{ substr($item->nama, 0, 2) }}
                            </div>
                            <div class="font-black text-gray-900 text-sm uppercase tracking-tight">{{ $item->nama }}</div>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="font-mono font-bold text-gray-600 text-xs bg-gray-50 px-2 py-1 rounded inline-block border border-gray-100 uppercase italic">{{ $item->no_sim }}</div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="text-[13px] font-bold text-gray-400 tracking-tight">{{ $item->user->email ?? 'No Email' }}</div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        @php
                            $badge = match($item->status) {
                                'Tersedia' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'Bekerja' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'Tidak Tersedia' => 'bg-rose-100 text-rose-700 border-rose-200',
                                default => 'bg-gray-100 text-gray-600 border-gray-200'
                            };
                        @endphp
                        <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $badge }}">
                            {{ $item->status }}
                        </span>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end gap-3">
                            <button wire:click="showDetail({{ $item->id }})" class="p-2.5 text-slate-600 bg-slate-50 hover:bg-slate-600 hover:text-white rounded-xl transition-all border border-slate-100 shadow-sm" title="Riwayat Tugas">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>

                            @can('update-sopir')
                            <button wire:click="edit({{ $item->id }})" class="p-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all border border-cyan-100 shadow-sm" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            @endcan

                            @can('delete-sopir')
                            <button onclick="confirm('Hapus sopir dan akun ini secara permanen?') || event.stopImmediatePropagation()" wire:click="delete({{ $item->id }})" class="p-2.5 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100 shadow-sm" title="Hapus">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-8 py-24 text-center text-gray-400 text-sm font-medium italic">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Tidak ditemukan data sopir.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
        {{ $sopirs->links('components.pagination-info') }}
    </div>
</div>

{{-- 4. MODAL CREATE / EDIT --}}
@if($showModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
            <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                <div class="px-10 py-6 border-b border-gray-100 bg-white flex justify-between items-center sticky top-0 z-10">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">{{ $modalTitle }}</h3>
                        <p class="text-[11px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Biodata & Lisensi Pengemudi</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar bg-white">
                    
                    <!-- Nama Lengkap -->
                    <div class="group">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input wire:model.live.debounce.300ms="nama" type="text" 
                            class="w-full h-14 rounded-2xl bg-gray-50/50 px-6 font-bold text-gray-800 transition-all
                            @error('nama') border-rose-500 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border-gray-100 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror"
                            placeholder="Nama Lengkap Sesuai KTP">
                        @error('nama') 
                            <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $message }}
                            </span> 
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Nomor SIM -->
                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                Nomor SIM A / B1 <span class="text-rose-500">*</span>
                            </label>
                            <input wire:model.live.debounce.300ms="no_sim" type="text" 
                                class="w-full h-14 rounded-2xl bg-gray-50/50 px-6 font-bold text-gray-800 transition-all uppercase
                                @error('no_sim') border-rose-500 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border-gray-100 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror"
                                placeholder="Contoh: 1234-5678-90">
                            @error('no_sim') 
                                <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $message }}
                                </span> 
                            @enderror
                        </div>

                        <!-- Alamat Email -->
                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                Alamat Email <span class="text-rose-500">*</span>
                            </label>
                            <input wire:model.live.debounce.300ms="email" type="email" 
                                class="w-full h-14 rounded-2xl bg-gray-50/50 px-6 font-bold text-gray-800 transition-all
                                @error('email') border-rose-500 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border-gray-100 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror"
                                placeholder="driver@mail.com">
                            @error('email') 
                                <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $message }}
                                </span> 
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Password -->
                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                Password @if($isEditMode) <span class="lowercase font-medium opacity-60">(Opsional)</span> @else <span class="text-rose-500">*</span> @endif
                            </label>
                            <input wire:model.live.debounce.300ms="password" type="password" 
                                class="w-full h-14 rounded-2xl bg-gray-50/50 px-6 font-bold transition-all
                                @error('password') border-rose-500 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border-gray-100 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                            @error('password') 
                                <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $message }}
                                </span> 
                            @enderror
                        </div>

                        <!-- Konfirmasi Password -->
                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 group-focus-within:text-cyan-600 transition-colors">
                                Konfirmasi Password
                            </label>
                            <input wire:model.live.debounce.300ms="password_confirmation" type="password" 
                                class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50/50 px-6 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold transition-all">
                        </div>
                    </div>

                    <!-- Status Ketersediaan -->
                    <div class="group">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4 text-center">Status Ketersediaan <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <label class="flex items-center justify-center h-14 rounded-2xl border-2 cursor-pointer transition-all shadow-sm {{ $status === 'Tersedia' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 ring-4 ring-emerald-500/10' : 'border-gray-100 hover:border-emerald-200' }}">
                                <input type="radio" wire:model.live="status" value="Tersedia" class="hidden">
                                <span class="text-[10px] font-black uppercase tracking-widest">Siap Tugas</span>
                            </label>
                            <label class="flex items-center justify-center h-14 rounded-2xl border-2 cursor-pointer transition-all shadow-sm {{ $status === 'Bekerja' ? 'border-blue-500 bg-blue-50 text-blue-700 ring-4 ring-blue-500/10' : 'border-gray-100 hover:border-blue-200' }}">
                                <input type="radio" wire:model.live="status" value="Bekerja" class="hidden">
                                <span class="text-[10px] font-black uppercase tracking-widest">Sedang Jalan</span>
                            </label>
                            <label class="flex items-center justify-center h-14 rounded-2xl border-2 cursor-pointer transition-all shadow-sm {{ $status === 'Tidak Tersedia' ? 'border-rose-500 bg-rose-50 text-rose-700 ring-4 ring-rose-500/10' : 'border-gray-100 hover:border-rose-200' }}">
                                <input type="radio" wire:model.live="status" value="Tidak Tersedia" class="hidden">
                                <span class="text-[10px] font-black uppercase tracking-widest">Off / Sakit</span>
                            </label>
                        </div>
                        @error('status') 
                            <span class="text-rose-500 text-[10px] font-black uppercase mt-4 block tracking-widest text-center">{{ $message }}</span> 
                        @enderror
                    </div>

                </div>

                <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center rounded-2xl px-12 py-4 bg-cyan-600 text-xs font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em] leading-none disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="{{ $isEditMode ? 'update' : 'store' }}">Simpan Data</span>
                        <span wire:loading wire:target="{{ $isEditMode ? 'update' : 'store' }}">Menyimpan...</span>
                    </button>
                    <button type="button" wire:click="closeModal" class="inline-flex justify-center rounded-2xl px-8 py-4 bg-white text-xs font-black text-gray-400 hover:bg-gray-100 border border-gray-200 transition-all uppercase tracking-[0.2em] leading-none">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- 5. MODAL DETAIL RIWAYAT TUGAS --}}
@if($showDetailModal && $selectedSopir)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
            <div class="px-10 py-6 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-10">
                <div>
                    <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Profil & Riwayat Tugas</h3>
                    <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Detail Kinerja Personel</p>
                </div>
                <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar bg-white">
                
                {{-- INFO CARD --}}
                <div class="flex flex-col md:flex-row gap-6 p-6 bg-cyan-50 rounded-3xl border border-cyan-100 shadow-sm shadow-cyan-100/50">
                    <div class="h-20 w-20 bg-white rounded-2xl flex items-center justify-center text-cyan-600 shadow-sm text-2xl font-black border border-cyan-100 shrink-0 mx-auto md:mx-0">
                        {{ substr($selectedSopir->nama, 0, 2) }}
                    </div>
                    <div class="text-center md:text-left flex-1">
                        <div class="text-2xl font-black text-gray-900 uppercase italic tracking-tight">{{ $selectedSopir->nama }}</div>
                        <div class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mt-1">SIM ID: {{ $selectedSopir->no_sim }} &bull; {{ $selectedSopir->user->email }}</div>
                        
                        <div class="mt-4 flex flex-wrap justify-center md:justify-start gap-2">
                            <span class="px-4 py-1.5 bg-white border border-cyan-200 text-cyan-700 text-[10px] font-black rounded-xl uppercase tracking-widest shadow-sm">
                                Status: {{ $selectedSopir->status }}
                            </span>
                            <span class="px-4 py-1.5 bg-white border border-gray-200 text-gray-500 text-[10px] font-black rounded-xl uppercase tracking-widest shadow-sm">
                                Total {{ $selectedSopir->peminjaman->count() }} Tugas
                            </span>
                        </div>
                    </div>
                </div>

                {{-- TABEL RIWAYAT --}}
                <div class="space-y-4">
                    <h4 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">10 Penugasan Terakhir</h4>
                    <div class="divide-y divide-gray-100 border border-gray-100 rounded-[2rem] overflow-hidden bg-gray-50/30 shadow-inner">
                        @forelse($selectedSopir->peminjaman as $tugas)
                            <div class="px-8 py-5 hover:bg-white transition-colors group">
                                <div class="flex flex-col md:flex-row justify-between md:items-center gap-2">
                                    <div>
                                        <div class="text-xs font-black text-gray-900 uppercase italic group-hover:text-cyan-600 transition-colors">
                                            {{ $tugas->mobil->merek ?? 'Mobil Tidak Dikenal' }} 
                                            <span class="ml-2 text-[10px] font-bold text-gray-400 normal-case italic">[{{ $tugas->mobil->id ?? '?' }}]</span>
                                        </div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">
                                            {{ \Carbon\Carbon::parse($tugas->tanggal_sewa)->translatedFormat('d M Y') }} &rarr; {{ \Carbon\Carbon::parse($tugas->tanggal_kembali)->translatedFormat('d M Y') }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        @php
                                            $statusTugas = match($tugas->status) {
                                                'selesai' => 'text-emerald-500 bg-emerald-50 border-emerald-100',
                                                'berlangsung' => 'text-blue-500 bg-blue-50 border-blue-100',
                                                'sudah dibayar lunas' => 'text-cyan-500 bg-cyan-50 border-cyan-100',
                                                default => 'text-gray-400 bg-gray-50 border-gray-100'
                                            };
                                        @endphp
                                        <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $statusTugas }}">
                                            {{ str_replace('_', ' ', $tugas->status) }}
                                        </span>
                                        <div class="text-[9px] text-gray-300 font-bold mt-1 uppercase">ID #{{ $tugas->id }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center">
                                <div class="text-gray-300 text-xs font-bold uppercase tracking-widest italic leading-relaxed">Belum ada catatan riwayat penugasan untuk pengemudi ini.</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-gray-50/80 px-10 py-8 flex justify-end border-t border-gray-100 rounded-b-[2.5rem]">
                <button wire:click="closeModal" class="px-10 py-3 bg-gray-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-800 transition-all shadow-xl shadow-gray-200">Tutup Detail</button>
            </div>
        </div>
    </div>
</div>
@endif
</div>