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
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Pengecekan Armada</h1>
            <p class="text-sm text-gray-500 font-medium mt-2">Verifikasi kondisi fisik mobil pasca-pengembalian pelanggan.</p>
        </div>
    </div>
</div>

{{-- SECTION A: TABEL TUGAS PENDING --}}
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <div class="h-6 w-1.5 bg-amber-500 rounded-full"></div>
        <h2 class="text-sm font-black text-gray-800 uppercase tracking-[0.2em]">Menunggu Pengecekan</h2>
    </div>
    
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-amber-50/30 border-b border-gray-100">
                    <tr>
                        <th class="px-8 py-4 text-[10px] font-black text-amber-600 uppercase tracking-widest">ID</th>
                        <th class="px-8 py-4 text-[10px] font-black text-amber-600 uppercase tracking-widest">Penyewa</th>
                        <th class="px-8 py-4 text-[10px] font-black text-amber-600 uppercase tracking-widest">Armada</th>
                        <th class="px-8 py-4 text-[10px] font-black text-amber-600 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pendingReturns as $p)
                    <tr class="hover:bg-amber-50/10 transition-colors">
                        <td class="px-8 py-5 font-black text-xs text-gray-700">#{{ $p->id }}</td>
                        <td class="px-8 py-5 font-bold text-gray-800 text-xs uppercase">{{ $p->peminjaman->user->name ?? 'N/A' }}</td>
                        <td class="px-8 py-5 font-bold text-cyan-600 text-xs">{{ $p->peminjaman->mobil->merek }} [{{ $p->peminjaman->mobil->plat_nomor }}]</td>
                        <td class="px-8 py-5 text-right">
                            <button wire:click="createInspection({{ $p->id }})" class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-amber-100 active:scale-95">
                                Cek Sekarang
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-16 text-center text-gray-400 text-[10px] font-bold uppercase tracking-widest italic">Antrean pengecekan kosong.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- SECTION B: RIWAYAT INSPEKSI --}}
<div class="pt-10 space-y-6">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-3">
            <div class="h-6 w-1.5 bg-cyan-600 rounded-full"></div>
            <h2 class="text-sm font-black text-gray-800 uppercase tracking-[0.2em]">Log Riwayat Inspeksi</h2>
        </div>
        
        <div class="relative w-80">
            <input wire:model.live.debounce.300ms="search" type="text" class="w-full h-11 pl-11 pr-4 rounded-xl border-gray-100 bg-white shadow-sm focus:border-cyan-500 font-bold text-xs transition-all" placeholder="Cari Plat Nomor Armada...">
            <svg class="absolute left-4 top-3.5 w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Audit Oleh</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Unit Mobil</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Waktu Audit</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-center">Kondisi</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($inspections as $item)
                    <tr class="hover:bg-cyan-50/20 transition-colors group">
                        <td class="px-8 py-6 font-bold text-gray-800 text-xs">{{ $item->staff->name ?? 'Staff' }}</td>
                        <td class="px-8 py-6 font-black text-gray-900 text-xs">
                            {{ $item->mobil->merek ?? '?' }} 
                            <span class="text-cyan-600 font-mono ml-1">[{{ $item->mobil->plat_nomor ?? 'N/A' }}]</span>
                        </td>
                        <td class="px-8 py-6 text-xs font-bold text-gray-500">{{ \Carbon\Carbon::parse($item->inspection_date)->format('d M Y H:i') }}</td>
                        <td class="px-8 py-6 text-center">
                            @php
                                $badge = match($item->condition) {
                                    'Baik Sempurna' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                    'Rusak Berat' => 'bg-rose-50 text-rose-600 border-rose-100',
                                    'Perlu Perbaikan Ringan' => 'bg-amber-50 text-amber-600 border-amber-100',
                                    default => 'bg-gray-50 text-gray-500'
                                };
                            @endphp
                            <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $badge }}">
                                {{ $item->condition }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end gap-2">
                                <button wire:click="showDetail({{ $item->id }})" class="p-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all border border-cyan-100 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                                @can('delete-vehicle-inspection')
                                <button wire:confirm="Hapus data audit ini?" wire:click="delete({{ $item->id }})" class="p-2.5 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-24 text-center text-gray-400 text-sm font-medium italic">Belum ada riwayat inspeksi armada.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
            {{ $inspections->links('components.pagination-info') }}
        </div>
    </div>
</div>

{{-- 4. MODAL PENGECEKAN (DETAIL UI) --}}
@if($showModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full border border-gray-100">
            <form wire:submit.prevent="store" x-data="{ 
                lateFine: @entangle('lateFine'), 
                damageCost: @entangle('damage_cost'), 
                get totalFine() { return Number(this.lateFine) + Number(this.damageCost) } 
            }">
                <div class="px-10 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">Detail Pengecekan Armada</h3>
                        <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Audit Fisik & Kalkulasi Denda Akhir</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    
                    {{-- 1. INFORMASI DASAR --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Penyewa</p>
                            <p class="font-bold text-gray-800 text-sm uppercase">{{ $infoPenyewa }}</p>
                        </div>
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Armada</p>
                            <p class="font-bold text-gray-800 text-sm uppercase">{{ $infoMobil }}</p>
                        </div>
                        <div class="bg-indigo-50 p-5 rounded-2xl border border-indigo-100">
                            <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Harga Sewa / Hari</p>
                            <p class="font-black text-indigo-700 text-sm uppercase">Rp {{ number_format($hargaPerHari) }}</p>
                        </div>
                    </div>

                    {{-- 2. DENDA KETERLAMBATAN --}}
                    <div class="p-6 rounded-[2rem] border-2 {{ $jamTerlambat > 0 ? 'bg-rose-50 border-rose-100' : 'bg-emerald-50 border-emerald-100' }}">
                        <h4 class="text-[11px] font-black uppercase tracking-widest mb-4 flex items-center gap-2 {{ $jamTerlambat > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            1. Denda Keterlambatan
                        </h4>
                        
                        @if($jamTerlambat > 0)
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-xs font-bold text-rose-700">Terlambat: <span class="text-lg font-black">{{ $jamTerlambat }} Jam</span></p>
                                <p class="text-[9px] text-rose-400 mt-1 uppercase font-black">Rumus: (Harga Harian x 10%) x Jam Terlambat</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest">Subtotal Denda Jam</p>
                                <p class="text-2xl font-black text-rose-600">Rp {{ number_format($lateFine) }}</p>
                            </div>
                        </div>
                        @else
                        <p class="text-xs font-bold text-emerald-700 uppercase tracking-wide">Pengembalian Tepat Waktu (Tidak ada denda keterlambatan).</p>
                        @endif
                    </div>

                    {{-- 3. LAPORAN KERUSAKAN --}}
                    <div class="p-6 rounded-[2rem] border-2 bg-gray-50 border-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-[11px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                2. Laporan Kerusakan & Biaya
                            </h4>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <span class="text-[10px] font-black text-gray-400 group-hover:text-cyan-600 uppercase tracking-widest">Temukan Kerusakan?</span>
                                <div class="relative inline-block w-12 h-6 transition duration-200 ease-in-out">
                                    <input type="checkbox" wire:model.live="isDamaged" class="absolute w-full h-full opacity-0 cursor-pointer z-10 peer">
                                    <div class="w-full h-full bg-gray-200 rounded-full peer-checked:bg-rose-500 transition-colors"></div>
                                    <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-6 shadow-md"></div>
                                </div>
                            </label>
                        </div>

                        @if($isDamaged)
                        <div class="space-y-6 animate-fade-in-down border-t border-dashed pt-6">
                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Deskripsi Kerusakan</label>
                                <textarea wire:model="damage_description" class="w-full h-24 rounded-2xl border-gray-100 bg-white p-4 focus:border-rose-500 font-bold text-gray-700 text-sm transition-all" placeholder="Contoh: Bumper depan penyok, spion kiri patah..."></textarea>
                            </div>
                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Estimasi Biaya Perbaikan (Rp)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 font-black">Rp</div>
                                    <input wire:model.live="damage_cost" type="number" class="w-full h-14 pl-12 rounded-2xl border-gray-100 bg-white px-5 focus:border-rose-500 font-black text-lg text-gray-800 transition-all shadow-inner">
                                </div>
                            </div>
                        </div>
                        @else
                        <p class="text-xs font-bold text-gray-400 italic">Klik toggle di samping jika ditemukan kerusakan fisik baru.</p>
                        @endif
                    </div>

                    {{-- 4. HASIL INSPEKSI STAFF --}}
                    <div class="space-y-6 pt-4 border-t border-dashed">
                        <h4 class="text-[11px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            3. Keputusan Kelayakan Armada
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Kondisi Umum Mobil</label>
                                <select wire:model="condition" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-cyan-500 font-black text-xs uppercase tracking-widest transition-all">
                                    <option value="Baik Sempurna">BAIK SEMPURNA (READY)</option>
                                    <option value="Perlu Perbaikan Ringan">PERLU PERBAIKAN RINGAN</option>
                                    <option value="Rusak Berat">RUSAK BERAT (WORKSHOP)</option>
                                </select>
                            </div>
                            <div class="group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Waktu Audit</label>
                                <input wire:model="inspection_date" type="datetime-local" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-5 focus:border-cyan-500 font-bold text-gray-700 transition-all">
                            </div>
                            <div class="md:col-span-2 group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Catatan Audit Internal</label>
                                <textarea wire:model="notes" class="w-full h-24 rounded-2xl border-gray-100 bg-gray-50 p-5 focus:border-cyan-500 font-bold text-gray-700 text-sm transition-all" placeholder="Misal: Bensin sudah diisi full kembali oleh user."></textarea>
                                @error('notes') <span class="text-rose-500 text-[10px] font-black mt-2 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="md:col-span-2 group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Foto Bukti Fisik</label>
                                <input wire:model="photo" type="file" class="block w-full text-[10px] text-gray-400 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100 transition-all">
                            </div>
                        </div>
                    </div>

                    {{-- 5. TOTAL BIAYA STICKY --}}
                    <div class="bg-gray-900 rounded-[2rem] p-8 text-white flex justify-between items-center shadow-2xl relative overflow-hidden">
                        <div class="absolute -right-10 -top-10 h-32 w-32 bg-cyan-500/10 rounded-full blur-3xl"></div>
                        <div>
                            <span class="text-[10px] font-black text-cyan-400 uppercase tracking-[0.4em] block mb-2">TOTAL AKHIR DENDA</span>
                            <span class="text-[8px] text-gray-400 uppercase tracking-widest">(Keterlambatan + Kerusakan)</span>
                        </div>
                        <div class="text-4xl font-black tracking-tighter">
                            Rp <span x-text="Number(totalFine).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                </div>

                <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                    <button type="submit" class="inline-flex justify-center rounded-2xl px-12 py-4 bg-cyan-600 text-xs font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em] leading-none">
                        Simpan Hasil Audit
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
                    <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Log Audit Armada #{{ $selectedInspection->id }}</h3>
                    <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1">Laporan Hasil Pengecekan Fisik</p>
                </div>
                <button wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 text-center">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Status Akhir</p>
                        <p class="font-black text-gray-800 text-xs uppercase">{{ $selectedInspection->condition }}</p>
                    </div>
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 text-center">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Diaudit Oleh</p>
                        <p class="font-black text-gray-800 text-xs uppercase">{{ $selectedInspection->staff->name }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <h5 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] border-b pb-2">Catatan Temuan</h5>
                    <div class="p-6 bg-cyan-50/50 rounded-[2rem] border border-cyan-100 text-cyan-900 font-bold text-xs leading-relaxed italic">
                        "{{ $selectedInspection->notes }}"
                    </div>
                </div>

                @if($selectedInspection->photo)
                <div class="space-y-4">
                    <h5 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] border-b pb-2">Lampiran Bukti Fisik</h5>
                    <img src="{{ asset('storage/' . $selectedInspection->photo) }}" class="w-full rounded-[2rem] shadow-lg border-4 border-white">
                </div>
                @endif
            </div>

            <div class="bg-gray-50/80 px-10 py-8 text-right border-t border-gray-100">
                <button wire:click="closeModal" class="px-10 py-4 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-gray-800 transition-all">Tutup Audit</button>
            </div>
        </div>
    </div>
</div>
@endif

<x-toast-notification />
</div>