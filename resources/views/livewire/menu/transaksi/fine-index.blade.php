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
        <div class="h-14 w-14 bg-rose-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-rose-100 ring-4 ring-rose-50">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Tagihan Denda</h1>
            <p class="text-sm text-gray-500 font-medium mt-2">Monitoring sanksi keterlambatan dan kerusakan armada.</p>
        </div>
    </div>
</div>

{{-- 2. CONTROL BAR --}}
<div class="bg-white p-2 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col lg:flex-row items-center justify-between gap-4">
    <div class="flex p-1 space-x-1 bg-gray-50 rounded-3xl w-full lg:w-auto overflow-x-auto">
        @foreach(['' => 'Semua', 'belum dibayar' => 'Belum Lunas', 'sudah dibayar' => 'Lunas'] as $key => $label)
        <button wire:click="$set('filterStatus', '{{ $key }}')"
            class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm whitespace-nowrap
            {{ $filterStatus === $key 
                ? 'bg-white text-rose-600 shadow-md ring-1 ring-black/5' 
                : 'text-gray-400 hover:text-gray-600 hover:bg-gray-100' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <div class="relative w-full lg:w-80 pr-2">
        <span class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-gray-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <input wire:model.live.debounce.300ms="search" type="text" 
            class="w-full h-12 pl-12 pr-6 rounded-2xl border-gray-100 bg-gray-50 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 font-bold text-xs transition-all placeholder:text-gray-300" 
            placeholder="Cari ID Denda, Penyewa, atau Plat...">
    </div>
</div>

{{-- 3. TABLE --}}
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mt-6">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left">
            <thead class="bg-rose-50/50 border-b border-gray-100">
                <tr>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-rose-600 uppercase tracking-[0.15em]">ID & Tgl</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-rose-600 uppercase tracking-[0.15em]">Penyewa</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-rose-600 uppercase tracking-[0.15em]">Rincian Denda</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-rose-600 uppercase tracking-[0.15em] text-center">Total Tagihan</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-rose-600 uppercase tracking-[0.15em] text-center">Status</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-rose-600 uppercase tracking-[0.15em] text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($fines as $fine)
                <tr class="hover:bg-rose-50/10 transition-colors group">
                    <td class="px-8 py-6">
                        <div class="font-black text-gray-900 text-sm tracking-tight">#{{ $fine->id }}</div>
                        <div class="text-[9px] font-bold text-gray-400 mt-1 uppercase">{{ \Carbon\Carbon::parse($fine->tanggal_terdeteksi)->format('d M Y') }}</div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="font-bold text-gray-800 text-xs uppercase">{{ $fine->peminjaman->user->name ?? 'N/A' }}</div>
                        <div class="text-[10px] mt-0.5 font-mono text-rose-400">{{ $fine->peminjaman->mobil->plat_nomor ?? '-' }}</div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex flex-col gap-1">
                            @if($fine->denda_keterlambatan > 0)
                                <span class="text-[10px] font-bold text-gray-600 flex justify-between w-32">
                                    <span>Late:</span> <span>Rp {{ number_format($fine->denda_keterlambatan) }}</span>
                                </span>
                            @endif
                            @if($fine->denda_kerusakan > 0)
                                <span class="text-[10px] font-bold text-gray-600 flex justify-between w-32">
                                    <span>Damage:</span> <span>Rp {{ number_format($fine->denda_kerusakan) }}</span>
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        <div class="font-black text-xs text-rose-600">Rp {{ number_format($fine->total_denda, 0, ',', '.') }}</div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        @php
                            $badge = match($fine->status) {
                                'sudah dibayar' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'belum dibayar' => 'bg-rose-100 text-rose-700 border-rose-200',
                                default => 'bg-gray-100 text-gray-600 border-gray-200'
                            };
                        @endphp
                        <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $badge }}">
                            {{ $fine->status }}
                        </span>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end gap-2">
                            <button wire:click="showDetail({{ $fine->id }})" class="p-2.5 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100 shadow-sm" title="Detail">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>

                            @if($fine->status == 'belum dibayar')
                                @can('update-fine')
                                <button wire:click="openPaymentModal({{ $fine->id }})" class="p-2.5 text-emerald-600 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-xl transition-all border border-emerald-100 shadow-sm" title="Bayar Denda">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </button>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-8 py-24 text-center text-gray-400 text-sm font-medium italic">Tidak ditemukan tagihan denda.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
        {{ $fines->links('components.pagination-info') }}
    </div>
</div>

{{-- 4. MODAL DETAIL --}}
@if($showDetailModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
            <div class="px-10 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Detail Denda #{{ $selectedFine->id }}</h3>
                    <p class="text-[10px] text-rose-600 font-bold uppercase tracking-[0.25em] mt-1">Rincian Sanksi & Tagihan</p>
                </div>
                <button wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-10 space-y-8">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Pelanggan</p>
                        <p class="font-black text-gray-800 text-sm uppercase">{{ $selectedFine->peminjaman->user->name }}</p>
                    </div>
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 text-right">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Tanggal Deteksi</p>
                        <p class="font-black text-gray-800 text-xs uppercase">{{ \Carbon\Carbon::parse($selectedFine->tanggal_terdeteksi)->format('d M Y') }}</p>
                    </div>
                </div>

                <div class="space-y-4 border-t border-dashed pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-500 uppercase">Denda Keterlambatan</span>
                        <span class="text-sm font-black text-gray-800">Rp {{ number_format($selectedFine->denda_keterlambatan) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-500 uppercase">Denda Kerusakan</span>
                        <span class="text-sm font-black text-gray-800">Rp {{ number_format($selectedFine->denda_kerusakan) }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                        <span class="text-sm font-black text-rose-600 uppercase tracking-widest">Total Tagihan</span>
                        <span class="text-2xl font-black text-rose-600">Rp {{ number_format($selectedFine->total_denda) }}</span>
                    </div>
                </div>

                @if($selectedFine->keterangan)
                <div class="p-5 bg-rose-50 rounded-2xl border border-rose-100 text-rose-900 text-xs font-medium italic">
                    "{{ $selectedFine->keterangan }}"
                </div>
                @endif
            </div>

            <div class="bg-gray-50/80 px-10 py-8 text-right border-t border-gray-100">
                <button wire:click="closeModal" class="px-10 py-4 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-gray-800 transition-all">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- 5. MODAL PAYMENT --}}
@if($showPaymentModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100">
            <form wire:submit.prevent="processPayment">
                <div class="px-10 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Bayar Denda</h3>
                        <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Pelunasan Tagihan Sanksi</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-10 space-y-6">
                    <div class="bg-rose-50 border border-rose-100 p-6 rounded-2xl text-center">
                        <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-1">Total Yang Harus Dibayar</p>
                        <p class="text-3xl font-black text-rose-600">Rp {{ number_format($selectedFine->total_denda) }}</p>
                    </div>

                    <div class="group">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Metode Pembayaran</label>
                        <select wire:model="metode_pembayaran" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-emerald-500 font-bold text-sm text-gray-700 transition-all cursor-pointer">
                            <option value="cash">Tunai / Cash</option>
                            <option value="transfer">Transfer Manual</option>
                        </select>
                    </div>

                    <div class="group">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Catatan Pelunasan</label>
                        <input wire:model="keterangan_bayar" type="text" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-emerald-500 font-bold text-gray-700 transition-all" placeholder="Contoh: Diterima oleh Staff A...">
                    </div>
                </div>

                <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                    <button type="submit" class="inline-flex justify-center rounded-2xl px-12 py-4 bg-emerald-600 text-xs font-black text-white hover:bg-emerald-700 shadow-xl shadow-emerald-100 transition-all uppercase tracking-[0.2em] leading-none">
                        Konfirmasi Lunas
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

<x-toast-notification />
</div>