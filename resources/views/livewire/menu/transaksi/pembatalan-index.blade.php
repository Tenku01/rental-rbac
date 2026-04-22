<div>
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

        <div x-data="{ toasts: [] }" 
             @notify.window="
                let detail = $event.detail || {};
                let type = detail.type || (detail[0] && detail[0].type) || 'info';
                let message = detail.message || (detail[0] && detail[0].message) || '';
                
                let id = Date.now();
                toasts.push({ id: id, type: type, message: message });
                setTimeout(() => { toasts = toasts.filter(t => t.id !== id) }, 5000);
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
                <div class="h-14 w-14 bg-rose-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-rose-100 ring-4 ring-rose-50">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Pembatalan & Refund</h1>
                    <p class="text-sm text-gray-500 font-medium mt-2">Kelola pembatalan pesanan dan pengembalian dana pelanggan.</p>
                </div>
            </div>

            @can('create-pembatalan_pesanan')
            <button wire:click="create" class="bg-rose-600 hover:bg-rose-700 text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-rose-100 flex items-center transition-all transform hover:scale-105 uppercase tracking-[0.2em] text-[11px] leading-none">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                Batalkan Pesanan
            </button>
            @endcan
        </div>

        {{-- 2. CONTROL BAR --}}
        <div class="bg-white p-2 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col lg:flex-row items-center justify-between gap-4">
            <div class="flex p-1 space-x-1 bg-gray-50 rounded-3xl w-full lg:w-auto overflow-x-auto">
                @foreach(['' => 'Semua', 'refunded' => 'Sudah Refund', 'pending_refund' => 'Pending Refund', 'no_refund' => 'Tanpa Refund'] as $key => $label)
                <button wire:click="$set('filterStatus', '{{ $key }}')"
                    class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm whitespace-nowrap
                    {{ $filterStatus === $key ? 'bg-white text-rose-600 shadow-md' : 'text-gray-400 hover:text-gray-600' }}">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            <div class="relative w-full lg:w-96 pr-2">
                <span class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" 
                    class="w-full h-12 pl-12 pr-6 rounded-2xl border-gray-100 bg-gray-50 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 font-bold text-xs transition-all placeholder:text-gray-300" 
                    placeholder="Cari Pelanggan atau Transaksi...">
            </div>
        </div>

        {{-- 3. DATA TABLE --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mt-6">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left">
                    <thead class="bg-rose-50/50 border-b border-gray-100">
                        <tr>
                            <th class="px-8 py-5 text-[11px] font-extrabold text-rose-600 uppercase tracking-[0.15em]">ID Transaksi</th>
                            <th class="px-8 py-5 text-[11px] font-extrabold text-rose-600 uppercase tracking-[0.15em]">Pelanggan</th>
                            <th class="px-8 py-5 text-[11px] font-extrabold text-rose-600 uppercase tracking-[0.15em]">Tgl Batal</th>
                            <th class="px-8 py-5 text-[11px] font-extrabold text-rose-600 uppercase tracking-[0.15em] text-center">Nominal Refund</th>
                            <th class="px-8 py-5 text-[11px] font-extrabold text-rose-600 uppercase tracking-[0.15em] text-center">Status</th>
                            <th class="px-8 py-5 text-[11px] font-extrabold text-rose-600 uppercase tracking-[0.15em] text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($pembatalan as $item)
                        <tr class="hover:bg-rose-50/10 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="font-black text-gray-900 text-sm tracking-tight">#{{ $item->peminjaman_id }}</div>
                                <div class="text-[9px] font-bold text-gray-400 mt-1 uppercase">Oleh: <span class="{{ $item->dibatalkan_oleh == 'admin' ? 'text-rose-500' : 'text-blue-500' }}">{{ $item->dibatalkan_oleh }}</span></div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="font-bold text-gray-800 text-xs uppercase">{{ $item->peminjaman->user->name ?? 'N/A' }}</div>
                                <div class="text-[10px] text-gray-400 mt-0.5">{{ $item->peminjaman->mobil->id ?? 'N/A' }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="text-xs font-black text-gray-700">{{ \Carbon\Carbon::parse($item->dibatalkan_pada)->format('d M Y') }}</div>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <div class="font-black text-xs text-gray-900">Rp {{ number_format($item->jumlah_refund, 0, ',', '.') }}</div>
                                <span class="text-[9px] text-gray-400 font-bold">({{ floatval($item->persentase_refund * 100) }}%)</span>
                            </td>
                            <td class="px-8 py-6 text-center">
                                @php
                                    $badge = match($item->status_pengembalian_dana) {
                                        'refunded' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'pending_refund' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        'no_refund' => 'bg-gray-50 text-gray-500 border-gray-100',
                                        default => 'bg-gray-50 text-gray-500'
                                    };
                                    
                                    if ($item->status_pengembalian_dana === 'pending_refund') {
                                        $label = 'TERTUNDA/PROSES';
                                    } elseif ($item->status_pengembalian_dana === 'refunded') {
                                        $label = 'SUKSES';
                                    } else {
                                        $label = 'NON-REFUND';
                                    }
                                @endphp
                                <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $badge }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-2 items-center">
                                    @can('update-pembatalan_pesanan')
                                        {{-- Tombol Proses Jika Ada yang Pending --}}
                                        @if($item->status_pengembalian_dana === 'pending_refund')
                                            <button wire:click="openProcessModal({{ $item->id }})" class="p-2.5 text-emerald-600 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-xl transition-all border border-emerald-100 shadow-sm" title="Proses Pengembalian Dana Pelanggan">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            </button>
                                        @endif
                                    @endcan

                                    <button wire:click="showDetail({{ $item->id }})" class="p-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all border border-cyan-100 shadow-sm" title="Detail Riwayat">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-8 py-24 text-center text-gray-400 text-sm font-medium italic">Belum ada data pembatalan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
                {{ $pembatalan->links('components.pagination-info') }}
            </div>
        </div>

        {{-- 4. MODAL CREATE CANCELLATION (ADMIN INITIATED - 1 FORM BATAL + REFUND) --}}
        @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
                    <form wire:submit="store" wire:confirm="Pesanan akan dibatalkan, mobil ditarik ke garasi, dan rekaman refund (jika ada) akan dikirim ke Midtrans. Lanjutkan?">
                        <div class="px-10 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                            <div>
                                <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">Form Pembatalan</h3>
                                <p class="text-[10px] text-rose-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Pembatalan & Pencatatan Transaksi</p>
                            </div>
                            <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                            
                            <div class="group" x-data="{
                                open: false,
                                search: '',
                                selected: @entangle('peminjaman_id').live,
                                rentals: @js($cancellable_transactions->map(fn($r) => ['id' => $r->id, 'text' => '#'.$r->id.' - '.($r->user->name ?? 'N/A').' ('.($r->mobil->id ?? 'N/A').')', 'total' => $r->total_dibayarkan])->values()),
                                get filtered() {
                                    if (this.search === '') return this.rentals;
                                    return this.rentals.filter(r => r.text.toLowerCase().includes(this.search.toLowerCase()));
                                },
                                get label() {
                                    let r = this.rentals.find(x => x.id == this.selected);
                                    return r ? r.text : '-- Pilih Transaksi --';
                                }
                            }">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Pilih Transaksi Aktif</label>
                                <div class="relative">
                                    <button type="button" @click="open = !open" class="w-full h-14 rounded-2xl border border-gray-100 bg-gray-50 px-5 text-left font-bold text-gray-800 text-sm flex items-center justify-between transition-all focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10">
                                        <span x-text="label"></span>
                                        <svg class="w-5 h-5 text-gray-400" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>

                                    <div x-show="open" @click.away="open = false" class="absolute z-30 mt-2 w-full bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden" style="display:none;">
                                        <div class="p-3 border-b border-gray-50">
                                            <input x-model="search" type="text" class="w-full h-10 rounded-xl border-gray-100 bg-gray-50 px-4 text-xs font-bold text-gray-700 placeholder-gray-400 focus:border-rose-500 transition-all outline-none" placeholder="Cari ID atau Pelanggan...">
                                        </div>
                                        <div class="max-h-52 overflow-y-auto custom-scrollbar">
                                            <template x-for="r in filtered" :key="r.id">
                                                <div @click="selected = r.id; open = false; search = ''" 
                                                    class="px-5 py-4 cursor-pointer hover:bg-rose-50 hover:text-rose-700 transition-colors border-b border-gray-50 last:border-0 font-bold text-[11px] uppercase tracking-wider text-gray-600">
                                                    <span x-text="r.text"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                                @error('peminjaman_id') <span class="text-rose-500 text-[10px] font-black mt-2 block tracking-widest">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4" wire:loading.class="opacity-50">
                                <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Dibayarkan Customer</p>
                                    <p class="font-black text-gray-800 text-sm uppercase">Rp {{ number_format($total_dibayarkan_customer, 0, ',', '.') }}</p>
                                </div>
                                <div class="bg-rose-50 p-5 rounded-2xl border border-rose-100 text-right">
                                    <p class="text-[9px] font-black text-rose-400 uppercase tracking-widest mb-1">Estimasi Pencatatan Refund</p>
                                    <p class="font-black text-rose-600 text-lg uppercase">Rp {{ number_format($estimasi_refund, 0, ',', '.') }}</p>
                                </div>
                            </div>

                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Persentase Refund (%)</label>
                                
                                <div class="flex gap-2 mb-3">
                                    <button type="button" wire:click="$set('persentase_refund', 0)" class="flex-1 py-2.5 rounded-xl border {{ $persentase_refund == 0 ? 'bg-rose-600 text-white border-rose-600 shadow-lg shadow-rose-200' : 'bg-white text-gray-500 border-gray-100 hover:bg-rose-50 hover:text-rose-600' }} text-xs font-black transition-all">0%</button>
                                    <button type="button" wire:click="$set('persentase_refund', 25)" class="flex-1 py-2.5 rounded-xl border {{ $persentase_refund == 25 ? 'bg-rose-600 text-white border-rose-600 shadow-lg shadow-rose-200' : 'bg-white text-gray-500 border-gray-100 hover:bg-rose-50 hover:text-rose-600' }} text-xs font-black transition-all">25%</button>
                                    <button type="button" wire:click="$set('persentase_refund', 50)" class="flex-1 py-2.5 rounded-xl border {{ $persentase_refund == 50 ? 'bg-rose-600 text-white border-rose-600 shadow-lg shadow-rose-200' : 'bg-white text-gray-500 border-gray-100 hover:bg-rose-50 hover:text-rose-600' }} text-xs font-black transition-all">50%</button>
                                    <button type="button" wire:click="$set('persentase_refund', 75)" class="flex-1 py-2.5 rounded-xl border {{ $persentase_refund == 75 ? 'bg-rose-600 text-white border-rose-600 shadow-lg shadow-rose-200' : 'bg-white text-gray-500 border-gray-100 hover:bg-rose-50 hover:text-rose-600' }} text-xs font-black transition-all">75%</button>
                                    <button type="button" wire:click="$set('persentase_refund', 100)" class="flex-1 py-2.5 rounded-xl border {{ $persentase_refund == 100 ? 'bg-rose-600 text-white border-rose-600 shadow-lg shadow-rose-200' : 'bg-white text-gray-500 border-gray-100 hover:bg-rose-50 hover:text-rose-600' }} text-xs font-black transition-all">100%</button>
                                </div>

                                <div class="relative">
                                    <input wire:model.live.debounce.300ms="persentase_refund" type="number" min="0" max="100" step="0.01" 
                                        class="w-full h-14 pl-5 pr-16 rounded-2xl border-gray-100 bg-gray-50 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 font-black text-gray-800 text-lg transition-all outline-none" 
                                        placeholder="0">
                                    <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none">
                                        <span class="text-gray-400 font-black text-sm uppercase tracking-wider">Persen</span>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-2 font-bold">*Jika Sandbox menolak, sistem akan otomatis mencatatnya sebagai Failed/Pending dan pesanan tetap berhasil dibatalkan.</p>
                                @error('persentase_refund') <span class="text-rose-500 text-[10px] font-black mt-2 block tracking-widest">{{ $message }}</span> @enderror
                            </div>

                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Alasan Pembatalan</label>
                                <textarea wire:model="alasan" class="w-full h-32 rounded-[2rem] border-gray-100 bg-gray-50 p-6 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 font-bold text-gray-700 transition-all outline-none text-sm" placeholder="Contoh: Kendala armada mendadak, unit rusak..."></textarea>
                                @error('alasan') <span class="text-rose-500 text-[10px] font-black mt-2 block tracking-widest">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                            <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center rounded-2xl px-12 py-4 bg-rose-600 text-xs font-black text-white hover:bg-rose-700 shadow-xl shadow-rose-100 transition-all uppercase tracking-[0.2em] leading-none">
                                <span wire:loading.remove wire:target="store">Batalkan & Eksekusi Refund</span>
                                <span wire:loading wire:target="store">Sedang Memproses...</span>
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

        {{-- 5. MODAL PROSES REFUND DARI PELANGGAN (USER INITIATED) --}}
        @if($showProcessModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-emerald-100">
                    <form wire:submit="submitProcessRefund" wire:confirm="Selesaikan persetujuan refund ini?">
                        <div class="px-10 py-6 border-b border-emerald-100 bg-emerald-50/50 flex justify-between items-center">
                            <div>
                                <h3 class="text-2xl font-black text-emerald-900 uppercase tracking-tight">Proses Pengembalian Dana</h3>
                                <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Penyetujuan Refund & Transfer Pembatalan</p>
                            </div>
                            <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-emerald-100 hover:text-emerald-700 rounded-2xl transition-all">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                            <div class="bg-emerald-50 p-6 rounded-[2rem] border border-emerald-100 text-emerald-900 font-bold text-sm italic">
                                Alasan Pelanggan: "{{ $selectedPembatalan->alasan ?? 'Tidak ada alasan' }}"
                            </div>

                            <div class="grid grid-cols-2 gap-4" wire:loading.class="opacity-50">
                                <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Dibayarkan Customer</p>
                                    <p class="font-black text-gray-800 text-sm uppercase">Rp {{ number_format($total_dibayarkan_customer, 0, ',', '.') }}</p>
                                </div>
                                <div class="bg-emerald-50 p-5 rounded-2xl border border-emerald-100 text-right">
                                    <p class="text-[9px] font-black text-emerald-500 uppercase tracking-widest mb-1">Estimasi Pencairan</p>
                                    <p class="font-black text-emerald-600 text-lg uppercase">Rp {{ number_format($estimasi_refund, 0, ',', '.') }}</p>
                                </div>
                            </div>

                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Konfirmasi Persentase Refund (%)</label>
                                
                                <div class="flex gap-2 mb-3">
                                    <button type="button" wire:click="$set('persentase_refund', 0)" class="flex-1 py-2.5 rounded-xl border {{ $persentase_refund == 0 ? 'bg-emerald-600 text-white border-emerald-600 shadow-lg shadow-emerald-200' : 'bg-white text-gray-500 border-gray-100 hover:bg-emerald-50 hover:text-emerald-600' }} text-xs font-black transition-all">0%</button>
                                    <button type="button" wire:click="$set('persentase_refund', 25)" class="flex-1 py-2.5 rounded-xl border {{ $persentase_refund == 25 ? 'bg-emerald-600 text-white border-emerald-600 shadow-lg shadow-emerald-200' : 'bg-white text-gray-500 border-gray-100 hover:bg-emerald-50 hover:text-emerald-600' }} text-xs font-black transition-all">25%</button>
                                    <button type="button" wire:click="$set('persentase_refund', 50)" class="flex-1 py-2.5 rounded-xl border {{ $persentase_refund == 50 ? 'bg-emerald-600 text-white border-emerald-600 shadow-lg shadow-emerald-200' : 'bg-white text-gray-500 border-gray-100 hover:bg-emerald-50 hover:text-emerald-600' }} text-xs font-black transition-all">50%</button>
                                    <button type="button" wire:click="$set('persentase_refund', 75)" class="flex-1 py-2.5 rounded-xl border {{ $persentase_refund == 75 ? 'bg-emerald-600 text-white border-emerald-600 shadow-lg shadow-emerald-200' : 'bg-white text-gray-500 border-gray-100 hover:bg-emerald-50 hover:text-emerald-600' }} text-xs font-black transition-all">75%</button>
                                    <button type="button" wire:click="$set('persentase_refund', 100)" class="flex-1 py-2.5 rounded-xl border {{ $persentase_refund == 100 ? 'bg-emerald-600 text-white border-emerald-600 shadow-lg shadow-emerald-200' : 'bg-white text-gray-500 border-gray-100 hover:bg-emerald-50 hover:text-emerald-600' }} text-xs font-black transition-all">100%</button>
                                </div>

                                <div class="relative">
                                    <input wire:model.live.debounce.300ms="persentase_refund" type="number" min="0" max="100" step="0.01"
                                        class="w-full h-14 pl-5 pr-16 rounded-2xl border-gray-100 bg-gray-50 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 font-black text-gray-800 text-lg transition-all outline-none">
                                    <div class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none">
                                        <span class="text-gray-400 font-black text-sm uppercase tracking-wider">Persen</span>
                                    </div>
                                </div>
                                <p class="text-[10px] text-gray-400 mt-2 font-bold">*Pilih 0% jika kompensasi refund ditolak. Saldo akan hangus untuk rental.</p>
                            </div>
                        </div>

                        <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                            <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center items-center rounded-2xl px-12 py-4 bg-emerald-600 text-xs font-black text-white hover:bg-emerald-700 shadow-xl shadow-emerald-100 transition-all uppercase tracking-[0.2em] leading-none">
                                <span wire:loading.remove wire:target="submitProcessRefund">Setujui & Transfer Refund</span>
                                <span wire:loading wire:target="submitProcessRefund">Sedang Mengirim...</span>
                            </button>
                            <button type="button" wire:click="closeModal" class="inline-flex justify-center rounded-2xl px-8 py-4 bg-white text-xs font-black text-gray-400 hover:bg-gray-100 border border-gray-200 transition-all uppercase tracking-[0.2em] leading-none">
                                Tutup
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        {{-- 6. MODAL DETAIL --}}
        @if($showDetailModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                    <div class="px-10 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <div>
                            <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Detail Pembatalan</h3>
                            <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1">Status Refund & Alasan</p>
                        </div>
                        <button wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-cyan-50 hover:text-cyan-500 rounded-2xl transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Dibatalkan Oleh</p>
                                <p class="font-black text-gray-800 text-xs uppercase">{{ $selectedPembatalan->dibatalkan_oleh }}</p>
                            </div>
                            <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 text-right">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Tanggal Batal</p>
                                <p class="font-black text-gray-800 text-xs uppercase">{{ $selectedPembatalan->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <h5 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] border-b pb-2">Alasan Pembatalan</h5>
                            <div class="p-6 bg-blue-50/50 rounded-[2rem] border border-blue-100 text-blue-900 font-bold text-xs leading-relaxed italic">
                                "{{ $selectedPembatalan->alasan ?? 'Tidak ada keterangan.' }}"
                            </div>
                        </div>

                        <div class="p-6 bg-gray-900 rounded-[2rem] border-2 border-gray-800 flex justify-between items-center shadow-lg text-white">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Total Refund ({{ floatval($selectedPembatalan->persentase_refund * 100) }}%)</p>
                            </div>
                            <p class="text-2xl font-black text-emerald-400">Rp {{ number_format($selectedPembatalan->jumlah_refund, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50/80 px-10 py-8 text-right border-t border-gray-100">
                        <button wire:click="closeModal" class="px-10 py-4 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-gray-800 transition-all">Tutup Menu</button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <x-toast-notification />
    </div>
</div>