<div class="p-8 space-y-10 bg-[#f9fafb] min-h-screen font-inter">
<style>
@import url('https://www.google.com/search?q=https://fonts.googleapis.com/css2%3Ffamily%3DInter:wght%40300%3B400%3B500%3B600%3B700%3B800%3B900%26display%3Dswap');
.font-inter { font-family: 'Inter', sans-serif; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #0891b2; }
</style>

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
</div>

{{-- 2. CONTROL BAR --}}
<div class="bg-white p-4 rounded-3xl shadow-sm border border-gray-100 flex flex-col lg:flex-row items-center justify-between gap-4">
    <div class="relative w-full group">
        <span class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-gray-300 group-focus-within:text-cyan-500 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <input wire:model.live.debounce.300ms="search" type="text" 
            class="w-full h-12 pl-12 pr-6 rounded-2xl border-gray-100 bg-gray-50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-xs transition-all placeholder:text-gray-300" 
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
                        <div class="text-[10px] font-mono text-cyan-600 font-bold mt-1 uppercase">{{ $item->mobil_id }}</div>
                    </td>
                    <td class="px-8 py-6">
                        <span class="px-3 py-1 bg-gray-100 text-gray-600 text-[10px] font-black rounded-lg border border-gray-200 uppercase tracking-tighter">{{ $item->pengembalian_kode ?? 'N/A' }}</span>
                    </td>
                    <td class="px-8 py-6">
                        <p class="text-xs text-gray-600 line-clamp-2 max-w-xs font-medium italic">"{{ $item->damage_description }}"</p>
                    </td>
                    <td class="px-8 py-6 text-center">
                        <div class="text-xs font-black text-gray-900">Rp {{ number_format($item->damage_cost, 0, ',', '.') }}</div>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end gap-2">
                            <button wire:click="showDetail('{{ $item->kode_laporan }}')" class="p-2.5 text-slate-600 bg-slate-50 hover:bg-slate-600 hover:text-white rounded-xl transition-all border border-slate-100 shadow-sm" title="Detail">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>

                            @can('update-damage')
                            <button wire:click="edit('{{ $item->kode_laporan }}')" class="p-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all border border-cyan-100 shadow-sm" title="Edit Data">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            @endcan

                            @can('delete-damage')
                            <button wire:confirm="Hapus laporan ini secara permanen?" wire:click="delete('{{ $item->kode_laporan }}')" class="p-2.5 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100 shadow-sm">
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

{{-- 4. MODAL EDIT --}}
@if($showEditModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
            <form wire:submit.prevent="update">
                <div class="px-10 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">Koreksi Laporan Kerusakan</h3>
                        <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">ID Laporan: {{ $editingKode }}</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-10 space-y-8">
                    <div class="group">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Rincian Kerusakan Armada</label>
                        <textarea wire:model="damage_description" class="w-full h-32 rounded-[2rem] border-gray-100 bg-gray-50 p-6 focus:border-cyan-500 font-bold text-gray-700 text-sm transition-all" placeholder="Jelaskan bagian yang rusak secara detail..."></textarea>
                        @error('damage_description') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="group">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Nilai Ganti Rugi / Denda (Rp)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-gray-400 font-black">Rp</div>
                            <input wire:model="damage_cost" type="number" class="w-full h-16 pl-14 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-cyan-500 font-black text-xl text-gray-800 transition-all shadow-inner">
                        </div>
                        @error('damage_cost') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                    <button type="submit" class="inline-flex justify-center rounded-2xl px-12 py-4 bg-cyan-600 text-xs font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em] leading-none">
                        Simpan Perubahan
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
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Informasi Armada</p>
                        <p class="font-black text-gray-800 text-xs uppercase">{{ $selectedReport->mobil->merek }}</p>
                        <p class="text-[10px] font-mono text-cyan-600 font-bold mt-1 tracking-wider">[{{ $selectedReport->mobil_id }}]</p>
                    </div>
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 text-right">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Penyewa Terakhir</p>
                        <p class="font-black text-gray-800 text-xs uppercase">{{ $selectedReport->pengembalian->peminjaman->user->name ?? 'N/A' }}</p>
                        <p class="text-[10px] text-gray-400 mt-1 uppercase tracking-tighter">{{ $selectedReport->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <h5 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] border-b pb-2">Deskripsi Kerusakan</h5>
                    <div class="p-6 bg-amber-50/50 rounded-[2rem] border border-amber-100 text-amber-900 font-bold text-xs leading-relaxed italic">
                        "{{ $selectedReport->damage_description }}"
                    </div>
                </div>

                <div class="p-6 bg-rose-50 rounded-[2rem] border-2 border-rose-100 flex justify-between items-center shadow-inner">
                    <div>
                        <p class="text-[10px] font-black text-rose-400 uppercase tracking-[0.2em]">Total Biaya Ganti Rugi</p>
                    </div>
                    <p class="text-2xl font-black text-rose-600">Rp {{ number_format($selectedReport->damage_cost, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-gray-50/80 px-10 py-8 text-right border-t border-gray-100">
                <button wire:click="closeModal" class="px-10 py-4 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-gray-800 transition-all active:scale-95">Tutup Detail</button>
            </div>
        </div>
    </div>
</div>
@endif

<x-toast-notification />
</div>