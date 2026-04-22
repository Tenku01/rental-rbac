<div class="p-8 space-y-10 bg-[#f9fafb] min-h-screen font-inter">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
.font-inter { font-family: 'Inter', sans-serif; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #0891b2; }
</style>

{{-- 1. HEADER --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-gray-200 pb-8">
    <div class="flex items-center gap-5">
        <div class="h-14 w-14 bg-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-emerald-100 ring-4 ring-emerald-50">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Riwayat Pembayaran</h1>
            <p class="text-sm text-gray-500 font-medium mt-2">Log transaksi keuangan masuk dan keluar (Refund).</p>
        </div>
    </div>
    
    {{-- Tidak ada tombol Create karena otomatis by system --}}
    <div class="hidden md:block">
        <span class="inline-flex items-center px-4 py-2 rounded-xl bg-gray-100 text-gray-500 text-xs font-bold border border-gray-200">
            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
            Live Transaction Data
        </span>
    </div>
</div>

{{-- 2. CONTROL BAR --}}
<div class="bg-white p-2 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col lg:flex-row items-center justify-between gap-4">
    <!-- Filter Tabs -->
    <div class="flex p-1 space-x-1 bg-gray-50 rounded-3xl w-full lg:w-auto overflow-x-auto custom-scrollbar pb-2 lg:pb-1">
        @foreach(['' => 'Semua', 'settlement' => 'Berhasil', 'pending' => 'Pending', 'refunded' => 'Refund', 'failed' => 'Gagal', 'expire' => 'Expired'] as $key => $label)
        <button wire:click="$set('filterStatus', '{{ $key }}')"
            class="px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm whitespace-nowrap
            {{ $filterStatus === $key 
                ? 'bg-white text-emerald-600 shadow-md ring-1 ring-black/5' 
                : 'text-gray-400 hover:text-gray-600 hover:bg-gray-100' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    <!-- Search -->
    <div class="relative w-full lg:w-80 pr-2">
        <span class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-gray-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <input wire:model.live.debounce.300ms="search" type="text" 
            class="w-full h-12 pl-12 pr-6 rounded-2xl border-gray-100 bg-gray-50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 font-bold text-xs transition-all placeholder:text-gray-300" 
            placeholder="Cari ID Transaksi atau Penyewa...">
    </div>
</div>

{{-- 3. TABLE --}}
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mt-6">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left">
            <thead class="bg-emerald-50/50 border-b border-gray-100">
                <tr>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-emerald-600 uppercase tracking-[0.15em]">ID & Tipe</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-emerald-600 uppercase tracking-[0.15em]">Penyewa</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-emerald-600 uppercase tracking-[0.15em]">Ref. Peminjaman</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-emerald-600 uppercase tracking-[0.15em]">Tanggal</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-emerald-600 uppercase tracking-[0.15em] text-center">Nominal</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-emerald-600 uppercase tracking-[0.15em] text-center">Status</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-emerald-600 uppercase tracking-[0.15em] text-right">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($pembayaranList as $pembayaran)
                <tr class="hover:bg-emerald-50/10 transition-colors group">
                    <td class="px-8 py-6">
                        <!-- 🔹 Diperbarui: midtrans_transaction_id -> id_transaksi_midtrans -->
                        <div class="font-black text-gray-900 text-xs tracking-tight" title="{{ $pembayaran->id_transaksi_midtrans }}">
                            {{ Str::limit($pembayaran->id_transaksi_midtrans, 20) }}
                        </div>
                        <div class="text-[9px] font-bold text-gray-400 mt-1 uppercase">{{ $pembayaran->tipe_transaksi }}</div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="font-bold text-gray-800 text-xs uppercase">{{ $pembayaran->peminjaman->user->name ?? 'User Terhapus' }}</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">{{ $pembayaran->peminjaman->user->email ?? '-' }}</div>
                    </td>
                    <td class="px-8 py-6">
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-[10px] font-mono font-bold rounded border border-gray-200">
                            #{{ $pembayaran->peminjaman_id }}
                        </span>
                    </td>
                    <td class="px-8 py-6">
                        <div class="text-xs font-black text-gray-700">{{ $pembayaran->created_at->format('d M Y') }}</div>
                        <div class="text-[10px] text-gray-400 font-bold">{{ $pembayaran->created_at->format('H:i') }} WIB</div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        <div class="text-sm font-black {{ $pembayaran->tipe_transaksi == 'refund' ? 'text-rose-600' : 'text-emerald-600' }}">
                            <!-- 🔹 Diperbarui: amount -> jumlah -->
                            {{ $pembayaran->tipe_transaksi == 'refund' ? '-' : '+' }} Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                        </div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        @php
                            $badge = match($pembayaran->status) {
                                'settlement', 'capture' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'refunded' => 'bg-purple-100 text-purple-700 border-purple-200',
                                'deny', 'cancel', 'expire', 'failed' => 'bg-rose-100 text-rose-700 border-rose-200',
                                default => 'bg-gray-100 text-gray-600 border-gray-200'
                            };
                        @endphp
                        <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $badge }}">
                            {{ $pembayaran->status }}
                        </span>
                    </td>
                    <td class="px-8 py-6 text-right">
                        @can('read-payment_transactions')
                        <button wire:click="showDetail({{ $pembayaran->id }})" class="p-2 text-emerald-600 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-xl transition-all border border-emerald-100 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-8 py-24 text-center text-gray-400 text-sm font-medium italic">Tidak ditemukan riwayat pembayaran.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
        <!-- 🔹 Diperbarui: payments -> pembayaranList -->
        {{ $pembayaranList->links('components.pagination-info') }}
    </div>
</div>

{{-- 4. MODAL DETAIL (READ ONLY) --}}
@if($showDetailModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
            <div class="px-10 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Detail Transaksi</h3>
                    <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-[0.25em] mt-1">Audit Log Keuangan</p>
                </div>
                <button wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                {{-- Summary Card --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 p-6 bg-gray-900 rounded-[2rem] text-white flex justify-between items-center shadow-lg">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-1">Total Nominal</p>
                            <!-- 🔹 Diperbarui: amount -> jumlah -->
                            <p class="text-2xl font-black text-white">Rp {{ number_format($selectedPembayaran->jumlah, 0, ',', '.') }}</p>
                        </div>
                        <div class="text-right">
                            <span class="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-white/10 text-white border border-white/20">
                                {{ $selectedPembayaran->status }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">ID Transaksi (Midtrans/System)</p>
                        <!-- 🔹 Diperbarui: midtrans_transaction_id -> id_transaksi_midtrans -->
                        <p class="text-xs font-mono font-bold text-gray-800 break-all">{{ $selectedPembayaran->id_transaksi_midtrans }}</p>
                    </div>

                    <div class="p-5 bg-gray-50 rounded-2xl border border-gray-100">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Waktu Transaksi</p>
                        <p class="text-xs font-bold text-gray-800">{{ $selectedPembayaran->created_at->format('d M Y, H:i:s') }}</p>
                    </div>
                </div>

                {{-- JSON Response Viewer (Untuk Debugging/Audit) --}}
                <div class="space-y-3">
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Data Teknis (Gateway Response)</h4>
                    <div class="bg-slate-800 rounded-2xl p-4 overflow-x-auto border border-slate-700 shadow-inner">
                        <pre class="text-[10px] font-mono text-emerald-400 leading-relaxed whitespace-pre-wrap">{{ $rawJson }}</pre>
                    </div>
                </div>

                @if($selectedPembayaran->id_transaksi_awal)
                <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-xs text-rose-700 font-bold">Ini adalah transaksi REFUND dari ID #{{ $selectedPembayaran->id_transaksi_awal }}</p>
                </div>
                @endif
            </div>

            <div class="bg-gray-50/80 px-10 py-8 text-right border-t border-gray-100">
                <button wire:click="closeModal" class="px-10 py-4 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-emerald-700 transition-all">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

<x-toast-notification />
</div>