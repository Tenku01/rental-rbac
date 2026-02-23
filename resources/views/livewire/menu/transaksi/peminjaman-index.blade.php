<div class="p-8 space-y-10 bg-[#f9fafb] min-h-screen font-inter">
<style>
@import url('https://www.google.com/search?q=https://fonts.googleapis.com/css2%3Ffamily%3DInter:wght%40300%3B400%3B500%3B600%3B700%3B800%3B900%26display%3Dswap');
.font-inter { font-family: 'Inter', sans-serif; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #0891b2; }

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down { animation: fadeInDown 0.3s ease-out; }
</style>

{{-- 1. HEADER --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-gray-200 pb-8">
    <div class="flex items-center gap-5">
        <div class="h-14 w-14 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-cyan-100 ring-4 ring-cyan-50">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Data Peminjaman</h1>
            <p class="text-sm text-gray-500 font-medium mt-2">Monitoring transaksi sewa, pembayaran, dan penjadwalan operasional.</p>
        </div>
    </div>

    @can('create-peminjaman')
    <button wire:click="openCreateModal" class="bg-cyan-600 hover:bg-cyan-700 text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-cyan-100 flex items-center transition-all transform hover:scale-105 uppercase tracking-[0.2em] text-[11px] leading-none">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
        Booking Manual
    </button>
    @endcan
</div>

{{-- 2. CONTROL BAR --}}
<div class="bg-white p-2 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col lg:flex-row items-center justify-between gap-4">
    <div class="flex p-1 space-x-1 bg-gray-50 rounded-3xl w-full lg:w-auto overflow-x-auto custom-scrollbar">
        @foreach(['' => 'Semua', 'menunggu pembayaran' => 'Menunggu', 'pembayaran dp' => 'DP', 'sudah dibayar lunas' => 'Lunas', 'berlangsung' => 'Jalan', 'selesai' => 'Selesai'] as $key => $label)
        <button wire:click="$set('filterStatus', '{{ $key }}')"
            class="px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm whitespace-nowrap
            {{ $filterStatus === $key 
                ? 'bg-white text-cyan-600 shadow-md ring-1 ring-black/5' 
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
            class="w-full h-12 pl-12 pr-6 rounded-2xl border-gray-100 bg-gray-50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-xs transition-all placeholder:text-gray-300" 
            placeholder="Cari Transaksi...">
    </div>
</div>

{{-- 3. TABLE DATA --}}
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mt-6">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left">
            <thead class="bg-gray-50/80 border-b border-gray-100">
                <tr>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">ID & Tipe</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Penyewa</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Unit & Jadwal</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-center">Keuangan</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-center">Status</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($peminjaman as $item)
                <tr class="hover:bg-cyan-50/20 transition-colors group">
                    <td class="px-8 py-6">
                        <div class="font-black text-gray-900 text-sm">#{{ $item->id }}</div>
                        <div class="text-[9px] font-black uppercase px-2 py-0.5 rounded bg-gray-100 text-gray-500 inline-block mt-1">{{ $item->metode_pembayaran }}</div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="font-bold text-gray-800 text-xs">{{ $item->user->name ?? 'User Terhapus' }}</div>
                        <div class="text-[10px] text-gray-400 mt-0.5">{{ $item->user->email ?? '-' }}</div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-3">
                            <div>
                                <div class="font-bold text-gray-800 text-xs">{{ $item->mobil->merek ?? '?' }} {{ $item->mobil->tipe ?? '' }}</div>
                                <div class="text-[10px] font-mono text-cyan-600 font-bold">{{ $item->mobil->plat_nomor ?? 'N/A' }}</div>
                                <div class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{ \Carbon\Carbon::parse($item->tanggal_sewa)->format('d/m') }} - {{ \Carbon\Carbon::parse($item->tanggal_kembali)->format('d/m/y') }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-6 text-center">
                        <div class="font-black text-xs text-gray-800">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</div>
                        @if($item->sisa_bayar <= 0)
                            <span class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mt-1 block">Lunas</span>
                        @else
                            <div class="text-[9px] text-rose-500 font-bold mt-1">Sisa: Rp {{ number_format($item->sisa_bayar, 0, ',', '.') }}</div>
                        @endif
                    </td>
                    <td class="px-8 py-6 text-center">
                        @php
                            $st = $item->status;
                            $badge = match($st) {
                                'menunggu pembayaran' => 'bg-slate-100 text-slate-600 border-slate-200',
                                'pembayaran dp' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'sudah dibayar lunas' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'berlangsung' => 'bg-cyan-100 text-cyan-700 border-cyan-200',
                                'selesai' => 'bg-blue-100 text-blue-700 border-blue-200',
                                'dibatalkan' => 'bg-rose-100 text-rose-700 border-rose-200',
                                default => 'bg-gray-100 text-gray-500 border-gray-200'
                            };
                        @endphp
                        <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $badge }}">
                            {{ $st }}
                        </span>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end gap-2">
                            @if($item->sisa_bayar > 0 && $item->status != 'dibatalkan')
                                <button wire:click="openPaymentModal({{ $item->id }})" class="p-2.5 text-emerald-600 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-xl transition-all border border-emerald-100 shadow-sm" title="Catat Pelunasan">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </button>
                            @endif

                            <button wire:click="showDetail({{ $item->id }})" class="p-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all border border-cyan-100 shadow-sm" title="Kelola Status">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-8 py-24 text-center text-gray-400 text-sm font-medium italic">Belum ada data transaksi peminjaman.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
        {{ $peminjaman->links('components.pagination-info') }}
    </div>
</div>

{{-- 4. MODAL CREATE MANUAL (SYNCED LOGIC) --}}
@if($showCreateModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-5xl w-full border border-gray-100">
            <form wire:submit.prevent="storeManual">
                <div class="px-10 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">Buat Reservasi Manual</h3>
                        <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Pencatatan Booking Admin & Pembayaran Offline</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-10 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
                        
                        {{-- KOLOM 1: PELANGGAN & ARMADA --}}
                        <div class="space-y-8">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b pb-3">1. Subjek & Objek</h4>
                            
                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Pilih Pelanggan</label>
                                <select wire:model="user_id" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-gray-700 transition-all">
                                    <option value="">-- Cari Pelanggan --</option>
                                    @foreach($users_list as $u)
                                        <option value="{{ $u->id }}">{{ strtoupper($u->name) }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                                @error('user_id') <span class="text-rose-500 text-[10px] font-black mt-2 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Unit Mobil</label>
                                <select wire:model.live="mobil_id" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-gray-700 transition-all">
                                    <option value="">-- Pilih Armada Tersedia --</option>
                                    @foreach($mobils_list as $m)
                                        <option value="{{ $m->id }}">{{ $m->merek }} {{ $m->tipe }} [{{ $m->plat_nomor }}] - Rp {{ number_format($m->harga) }}</option>
                                    @endforeach
                                </select>
                                @error('mobil_id') <span class="text-rose-500 text-[10px] font-black mt-2 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- KOLOM 2: WAKTU SEWA --}}
                        <div class="space-y-8 lg:border-l lg:pl-10">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b pb-3">2. Penjadwalan</h4>
                            
                            <div class="grid grid-cols-1 gap-6">
                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Tanggal Mulai Sewa</label>
                                    <input wire:model.live="tanggal_sewa" type="date" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-cyan-500 font-bold text-gray-700 transition-all">
                                </div>

                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Tanggal Pengembalian</label>
                                    <input wire:model.live="tanggal_kembali" type="date" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-cyan-500 font-bold text-gray-700 transition-all">
                                    @error('tanggal_kembali') <span class="text-rose-500 text-[10px] font-black mt-2 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Jam Pengambilan & Pengembalian</label>
                                    <input wire:model.live="jam_sewa" type="time" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-cyan-500 font-black text-lg text-gray-800 transition-all">
                                </div>
                            </div>

                            {{-- RETURN NOTICE ALERT (LOGIKA SYNC) --}}
                            @if($return_notice)
                            <div class="bg-amber-50 border border-amber-100 p-4 rounded-2xl flex gap-4 items-start animate-fade-in-down shadow-sm">
                                <div class="h-8 w-8 bg-amber-500 rounded-full flex items-center justify-center text-white shrink-0 shadow-lg shadow-amber-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <p class="text-[11px] font-black text-amber-700 leading-relaxed uppercase tracking-tighter">{{ $return_notice }}</p>
                            </div>
                            @endif

                            {{-- SOPIR FILTERING --}}
                            <div class="pt-4 border-t border-dashed">
                                <label class="flex items-center gap-3 cursor-pointer group mb-4">
                                    <div class="relative inline-block w-12 h-6 transition duration-200 ease-in-out">
                                        <input type="checkbox" wire:model.live="add_on_sopir" class="absolute w-full h-full opacity-0 cursor-pointer z-10 peer">
                                        <div class="w-full h-full bg-gray-200 rounded-full peer-checked:bg-cyan-600 transition-colors"></div>
                                        <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-6 shadow-md"></div>
                                    </div>
                                    <span class="text-xs font-black text-gray-400 group-hover:text-cyan-600 uppercase tracking-widest">Gunakan Jasa Sopir</span>
                                </label>

                                @if($add_on_sopir)
                                <div class="animate-fade-in-down">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Pilih Sopir (Tersedia)</label>
                                    @if($sopirs_list->isEmpty())
                                        <div class="p-4 bg-rose-50 text-rose-600 text-[10px] font-black uppercase rounded-2xl border border-rose-100 text-center">
                                            Sopir Tidak Tersedia Pada Tanggal Tersebut
                                        </div>
                                    @else
                                        <select wire:model="sopir_id" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-cyan-500 font-bold text-gray-700 transition-all">
                                            <option value="">-- Pilih Sopir --</option>
                                            @foreach($sopirs_list as $s)
                                                <option value="{{ $s->id }}">{{ strtoupper($s->nama) }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- KOLOM 3: RINGKASAN & PEMBAYARAN --}}
                        <div class="space-y-8 lg:border-l lg:pl-10">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b pb-3">3. Billing</h4>
                            
                            <div class="bg-gray-900 rounded-[2rem] p-8 text-white relative overflow-hidden shadow-2xl">
                                <div class="absolute -right-5 -top-5 h-24 w-24 bg-cyan-500/20 rounded-full blur-3xl"></div>
                                <p class="text-[10px] font-black text-cyan-400 uppercase tracking-[0.3em] mb-3">Total Estimasi Harga</p>
                                <div class="text-4xl font-black tracking-tight">Rp {{ number_format($total_harga, 0, ',', '.') }}</div>
                            </div>

                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Input Uang Diterima (Cash)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400 font-black">Rp</div>
                                    <input wire:model="bayar_awal" type="number" class="w-full h-16 pl-12 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-cyan-500 font-black text-xl text-gray-800 transition-all shadow-inner" placeholder="0">
                                </div>
                                <p class="text-[9px] text-gray-400 mt-2 italic font-medium leading-relaxed">System akan otomatis menentukan status DP atau LUNAS berdasarkan nominal input.</p>
                            </div>

                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Unggah Bukti Kuitansi (Opsional)</label>
                                <input wire:model="bukti_bayar_awal" type="file" class="block w-full text-[10px] text-gray-400 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100 transition-all">
                            </div>
                        </div>

                    </div>
                </div>

                <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                    <button type="submit" class="inline-flex justify-center rounded-2xl px-12 py-4 bg-cyan-600 text-xs font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em] leading-none">
                        Simpan Reservasi
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

{{-- 5. MODAL DETAIL & STATUS MANAGEMENT --}}
@if($showDetailModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
            <form wire:submit.prevent="updateStatus">
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Kelola Transaksi #{{ $selectedPeminjaman->id }}</h3>
                        <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Detail History & Operasional Armada</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    
                    {{-- Detail Info Card --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                            <div class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Data Penyewa</div>
                            <div class="font-black text-gray-800 text-sm uppercase">{{ $selectedPeminjaman->user->name }}</div>
                            <div class="text-[10px] text-gray-400 mt-1">{{ $selectedPeminjaman->user->email }}</div>
                        </div>
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                            <div class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">Informasi Armada</div>
                            <div class="font-black text-gray-800 text-sm uppercase">{{ $selectedPeminjaman->mobil->merek }} {{ $selectedPeminjaman->mobil->tipe }}</div>
                            <div class="text-[10px] font-mono text-cyan-600 font-bold mt-1">{{ $selectedPeminjaman->mobil->plat_nomor }}</div>
                        </div>
                    </div>

                    {{-- History Transaksi Moneter --}}
                    <div class="rounded-2xl border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50 px-5 py-3 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Riwayat Pembayaran Kas & Online</div>
                        <div class="divide-y divide-gray-50 max-h-40 overflow-y-auto custom-scrollbar">
                            @forelse($selectedPeminjaman->paymentTransactions as $pay)
                                <div class="px-5 py-4 flex justify-between items-center hover:bg-gray-50/50 transition-colors">
                                    <div>
                                        <div class="text-[10px] font-black text-gray-700 uppercase tracking-widest">{{ str_replace('_', ' ', $pay->tipe_transaksi) }}</div>
                                        <div class="text-[9px] text-gray-400 mt-1 uppercase">{{ $pay->created_at->format('d M Y H:i') }} | ID: {{ $pay->midtrans_transaction_id }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-black text-gray-800">Rp {{ number_format($pay->amount, 0, ',', '.') }}</div>
                                        <span class="text-[8px] font-black uppercase text-emerald-500 bg-emerald-50 px-1.5 py-0.5 rounded">{{ $pay->status }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-10 text-center text-gray-400 text-[10px] uppercase font-bold italic">Belum ada mutasi pembayaran.</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Update Status Operasional --}}
                    <div class="pt-6 border-t border-dashed border-gray-200">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-[0.25em] mb-4">Ubah Status Operasional</label>
                        <select wire:model="status_peminjaman_edit" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-900 text-white px-5 focus:border-cyan-500 font-black text-xs uppercase tracking-widest cursor-pointer transition-all">
                            <option value="menunggu pembayaran">Menunggu Pembayaran</option>
                            <option value="pembayaran dp">Pembayaran DP</option>
                            <option value="sudah dibayar lunas">Pembayaran Lunas</option>
                            <option value="berlangsung">Sedang Berjalan (Jalan)</option>
                            <option value="selesai">Selesai (Kembali)</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                        <div class="mt-4 p-4 bg-cyan-50 rounded-xl flex gap-3 items-center">
                            <svg class="w-4 h-4 text-cyan-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-[9px] font-bold text-cyan-700 leading-relaxed uppercase">Mengubah status ke "Berlangsung" akan mengunci status armada di database menjadi "DISEWA".</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                    <button type="submit" class="inline-flex justify-center rounded-2xl px-12 py-4 bg-cyan-600 text-xs font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em] leading-none">
                        Update Status
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

{{-- 6. MODAL PELUNASAN MANUAL --}}
@if($showPaymentModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100">
            <form wire:submit.prevent="storePayment">
                <div class="px-8 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Penerimaan Dana</h3>
                        <p class="text-[10px] text-emerald-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Input Pembayaran Cicilan / Pelunasan Manual</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-10 space-y-8">
                    <div class="bg-rose-50 border-2 border-rose-100 p-6 rounded-[2rem] text-center shadow-inner">
                        <p class="text-[10px] font-black text-rose-400 uppercase tracking-[0.2em] mb-2">Tunggakan Pembayaran</p>
                        <p class="text-3xl font-black text-rose-600">Rp {{ number_format($selectedPeminjaman->sisa_bayar ?? 0, 0, ',', '.') }}</p>
                    </div>

                    <div class="group">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Nominal Setoran</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400 font-black">Rp</div>
                            <input wire:model="payment_amount" type="number" class="w-full h-16 pl-12 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-emerald-500 font-black text-2xl text-gray-800 transition-all shadow-inner">
                        </div>
                        @error('payment_amount') <span class="text-rose-500 text-[10px] font-black mt-2 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="group">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Catatan Pembayaran</label>
                        <input wire:model="payment_note" type="text" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-emerald-500 font-bold text-gray-700 transition-all" placeholder="Contoh: Pelunasan sisa sewa secara tunai">
                    </div>
                </div>

                <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                    <button type="submit" class="inline-flex justify-center rounded-2xl px-12 py-4 bg-emerald-600 text-xs font-black text-white hover:bg-emerald-700 shadow-xl shadow-emerald-100 transition-all uppercase tracking-[0.2em] leading-none">
                        Proses Setoran
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