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
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
        </div>
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Logbook Aktivitas Sopir</h1>
            <p class="text-sm text-gray-500 font-medium mt-2">Pemantauan riwayat perjalanan dan status operasional driver secara real-time.</p>
        </div>
    </div>

    @can('create-logbook')
    <button wire:click="create" class="bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-black py-4 px-10 rounded-2xl shadow-xl shadow-cyan-100 flex items-center transition-all transform hover:scale-105 uppercase tracking-[0.2em] text-[11px] leading-none">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
        Tambah Log Baru
    </button>
    @endcan
</div>

{{-- 2. CONTROL BAR --}}
<div class="bg-white p-4 rounded-3xl shadow-sm border border-gray-100 flex flex-col lg:flex-row items-center justify-between gap-4">
    <div class="relative w-full group">
        <span class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-gray-300 group-focus-within:text-cyan-500 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </span>
        <input wire:model.live.debounce.300ms="search" type="text" 
            class="w-full h-12 pl-12 pr-6 rounded-2xl border-gray-100 bg-gray-50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-xs transition-all placeholder:text-gray-300" 
            placeholder="Cari berdasarkan nama sopir atau nomor plat kendaraan...">
    </div>
</div>

{{-- 3. DATA TABLE --}}
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mt-6">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left">
            <thead class="bg-gray-50/80 border-b border-gray-100">
                <tr>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Sopir & Armada</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Waktu Aktivitas</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Catatan Aktivitas</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-center">Status</th>
                    <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($logs as $log)
                <tr class="hover:bg-cyan-50/20 transition-colors group">
                    <td class="px-8 py-6">
                        <div class="font-black text-gray-900 text-sm tracking-tight capitalize">{{ $log->peminjaman->sopir->nama ?? 'N/A' }}</div>
                        <div class="text-[10px] font-mono text-cyan-600 font-bold mt-1 uppercase tracking-tighter">{{ $log->peminjaman->mobil->plat_nomor ?? 'N/A' }}</div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="text-xs font-black text-gray-700 uppercase">{{ \Carbon\Carbon::parse($log->tanggal_aktivitas)->format('d M Y') }}</div>
                        <div class="text-[10px] text-gray-400 font-bold mt-1 uppercase">{{ $log->waktu_log->format('H:i') }} WIB</div>
                    </td>
                    <td class="px-8 py-6">
                        <p class="text-xs text-gray-600 line-clamp-1 max-w-xs font-medium italic">"{{ $log->deskripsi_aktivitas }}"</p>
                    </td>
                    <td class="px-8 py-6 text-center">
                        @php
                            $badge = match($log->status_log) {
                                'mulai_kerja' => 'bg-blue-50 text-blue-600 border-blue-100',
                                'dalam_perjalanan' => 'bg-amber-50 text-amber-600 border-amber-100',
                                'selesai_hari_ini' => 'bg-purple-50 text-purple-600 border-purple-100',
                                'selesai_peminjaman' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                default => 'bg-gray-50 text-gray-500'
                            };
                        @endphp
                        <span class="inline-flex px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $badge }}">
                            {{ str_replace('_', ' ', $log->status_log) }}
                        </span>
                    </td>
                    <td class="px-8 py-6 text-right">
                        <div class="flex justify-end gap-2">
                            <button wire:click="showDetail({{ $log->id }})" class="p-2.5 text-slate-600 bg-slate-50 hover:bg-slate-600 hover:text-white rounded-xl transition-all border border-slate-100 shadow-sm" title="Detail">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </button>

                            @can('update-logbook')
                            <button wire:click="edit({{ $log->id }})" class="p-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all border border-cyan-100 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            @endcan

                            @can('delete-logbook')
                            <button wire:confirm="Hapus log aktivitas ini?" wire:click="delete({{ $log->id }})" class="p-2.5 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-8 py-24 text-center text-gray-400 text-sm font-medium italic uppercase tracking-widest">Belum ada catatan aktivitas sopir.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
        {{ $logs->links('components.pagination-info') }}
    </div>
</div>

{{-- 4. MODAL CREATE / EDIT --}}
@if($showModal)
<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl w-full border border-gray-100">
            <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                <div class="px-10 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">{{ $modalTitle }}</h3>
                        <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Dokumentasi Operasional Sopir Harian</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-10 space-y-8 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    
                    {{-- Searchable Selection (Alpine.js) --}}
                    @if(!$isEditMode)
                    <div class="group" x-data="{
                        open: false,
                        search: '',
                        selected: @entangle('peminjaman_id'),
                        rentals: {{ $active_rentals->map(fn($r) => ['id' => $r->id, 'text' => strtoupper($r->sopir->nama ?? 'Sopir').' - '.$r->mobil->plat_nomor, 'merek' => $r->mobil->merek, 'plat' => $r->mobil->plat_nomor, 'start' => \Carbon\Carbon::parse($r->tanggal_sewa)->format('d M Y'), 'end' => \Carbon\Carbon::parse($r->tanggal_kembali)->format('d M Y')])->values()->toJson() }},
                        get filtered() {
                            if (this.search === '') return this.rentals;
                            return this.rentals.filter(r => r.text.toLowerCase().includes(this.search.toLowerCase()));
                        },
                        get selectedRental() {
                            return this.rentals.find(x => x.id == this.selected);
                        },
                        get label() {
                            return this.selectedRental ? this.selectedRental.text : '-- Pilih Sopir & Armada Aktif --';
                        }
                    }">
                        <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Penugasan Sopir Aktif</label>
                        <div class="relative">
                            <button type="button" @click="open = !open" class="w-full h-14 rounded-2xl border border-gray-100 bg-gray-50 px-6 text-left font-bold text-gray-800 text-sm flex items-center justify-between transition-all focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                                <span x-text="label"></span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div x-show="open" @click.away="open = false" class="absolute z-30 mt-2 w-full bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden" style="display:none;">
                                <div class="p-3 border-b border-gray-50">
                                    <input x-model="search" type="text" class="w-full h-10 rounded-xl border-gray-100 bg-gray-50 px-4 text-xs font-bold text-gray-700 placeholder-gray-400 focus:border-cyan-500 transition-all" placeholder="Cari Nama Sopir atau Plat...">
                                </div>
                                <div class="max-h-52 overflow-y-auto custom-scrollbar">
                                    <template x-for="r in filtered" :key="r.id">
                                        <div @click="selected = r.id; open = false; search = ''" 
                                            class="px-6 py-4 cursor-pointer hover:bg-cyan-50 hover:text-cyan-700 transition-colors border-b border-gray-50 last:border-0 font-bold text-[11px] uppercase tracking-wider text-gray-600">
                                            <span x-text="r.text"></span>
                                        </div>
                                    </template>
                                    <div x-show="filtered.length === 0" class="p-5 text-center text-[10px] font-bold text-gray-400 uppercase italic">Tidak ada sopir yang sedang bertugas.</div>
                                </div>
                            </div>
                        </div>

                        {{-- Info Box (Tampil saat dipilih) --}}
                        <div x-show="selected" class="mt-6 animate-fade-in-down">
                            <div class="bg-gradient-to-r from-cyan-50 to-blue-50 px-6 py-5 rounded-2xl shadow-sm border border-cyan-100 flex justify-between items-center">
                                <div>
                                    <p class="text-[9px] text-gray-500 uppercase font-black tracking-widest">Mobil Tugas</p>
                                    <p class="text-lg font-black text-cyan-700 mt-1 uppercase" x-text="selectedRental ? selectedRental.merek + ' - ' + selectedRental.plat : ''"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] text-gray-500 uppercase font-black tracking-widest">Periode Sewa</p>
                                    <div class="flex items-center mt-1 text-xs font-bold text-gray-600 gap-2">
                                        <span x-text="selectedRental ? selectedRental.start : ''"></span>
                                        <span class="text-cyan-300">→</span>
                                        <span x-text="selectedRental ? selectedRental.end : ''"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @error('peminjaman_id') <span class="text-rose-500 text-[10px] font-black mt-2 block tracking-widest">{{ $message }}</span> @enderror
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Status Aktivitas</label>
                            <select wire:model="status_log" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-6 focus:border-cyan-500 font-black text-[11px] uppercase tracking-widest cursor-pointer transition-all">
                                <option value="mulai_kerja">🚀 MULAI KERJA (ABSEN)</option>
                                <option value="dalam_perjalanan">📍 DALAM PERJALANAN</option>
                                <option value="selesai_hari_ini">🏠 SELESAI HARI INI</option>
                                <option value="selesai_peminjaman">🏁 SELESAI PENUGASAN (PULANG)</option>
                            </select>
                        </div>

                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Tanggal Aktivitas</label>
                            <input wire:model="tanggal_aktivitas" type="date" class="w-full h-14 rounded-2xl border-gray-100 bg-gray-50 px-6 focus:border-cyan-500 font-bold text-gray-700 transition-all">
                        </div>

                        <div class="md:col-span-2 group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Deskripsi Kegiatan</label>
                            <textarea wire:model="deskripsi_aktivitas" class="w-full h-32 rounded-[2rem] border-gray-100 bg-gray-50 p-6 focus:border-cyan-500 font-bold text-gray-700 text-sm transition-all shadow-inner" placeholder="Misal: Penjemputan di Bandara, Menuju lokasi wisata X..."></textarea>
                            @error('deskripsi_aktivitas') <span class="text-rose-500 text-[10px] font-black mt-2 block tracking-widest">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2 group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Foto Bukti Perjalanan (Opsional)</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-[2rem] hover:border-cyan-400 hover:bg-cyan-50 transition duration-200">
                                <div class="space-y-2 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-cyan-600 hover:text-cyan-700 focus-within:outline-none">
                                            <span class="px-6 py-2 bg-cyan-100 rounded-xl hover:bg-cyan-200 transition font-black uppercase text-[10px] tracking-widest">Pilih file Gambar</span>
                                            <input type="file" wire:model="foto_bukti" class="sr-only" accept="image/*">
                                        </label>
                                    </div>
                                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-2">PNG, JPG up to 2MB</p>
                                </div>
                            </div>
                            @error('foto_bukti') <span class="text-rose-500 text-[10px] font-black mt-2 block tracking-widest">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                    <button type="submit" class="inline-flex justify-center rounded-2xl px-12 py-4 bg-gradient-to-r from-cyan-600 to-blue-600 text-xs font-black text-white hover:from-cyan-700 hover:to-blue-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em] leading-none">
                        {{ $isEditMode ? 'Simpan Perubahan' : 'Catat Aktivitas' }}
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
                    <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Detail Log Aktivitas #{{ $selectedLog->id }}</h3>
                    <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Histori Perjalanan Armada</p>
                </div>
                <button wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Nama Sopir</p>
                        <p class="font-black text-gray-800 text-xs uppercase">{{ $selectedLog->peminjaman->sopir->nama ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 text-right">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Armada</p>
                        <p class="font-black text-gray-800 text-xs uppercase">{{ $selectedLog->peminjaman->mobil->merek ?? 'N/A' }}</p>
                        <p class="text-[10px] font-mono text-cyan-600 font-bold mt-1 tracking-wider">[{{ $selectedLog->peminjaman->mobil->plat_nomor ?? 'N/A' }}]</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="h-8 w-8 rounded-full flex items-center justify-center text-sm
                            {{ match($selectedLog->status_log) {
                                'selesai_peminjaman' => 'bg-green-500',
                                'mulai_kerja' => 'bg-blue-500',
                                'selesai_hari_ini' => 'bg-purple-500',
                                default => 'bg-cyan-500'
                            } }} text-white">
                            {{ match($selectedLog->status_log) {
                                'selesai_peminjaman' => '🏁',
                                'mulai_kerja' => '🚀',
                                'selesai_hari_ini' => '🏠',
                                default => '📍'
                            } }}
                        </span>
                        <h5 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Status: {{ str_replace('_', ' ', $selectedLog->status_log) }}</h5>
                    </div>
                    <div class="p-6 bg-cyan-50/50 rounded-[2rem] border border-cyan-100 text-cyan-900 font-bold text-xs leading-relaxed italic shadow-inner">
                        "{{ $selectedLog->deskripsi_aktivitas }}"
                    </div>
                </div>

                @if($selectedLog->foto_bukti)
                <div class="space-y-4">
                    <h5 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] border-b pb-2">Foto Dokumentasi</h5>
                    <img src="{{ asset('storage/' . $selectedLog->foto_bukti) }}" class="w-full rounded-[2.5rem] shadow-xl border-4 border-white transition-transform hover:scale-[1.02]">
                </div>
                @endif
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