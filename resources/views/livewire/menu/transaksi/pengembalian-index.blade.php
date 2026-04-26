<div class="p-8 space-y-10 bg-[#f9fafb] min-h-screen font-inter">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
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
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Data Pengembalian</h1>
                <p class="text-sm text-gray-500 font-medium mt-2">Penyelesaian transaksi dan pengecekan kondisi armada kembali.</p>
            </div>
        </div>

        @can('create-pengembalian')
        <button wire:click="create" class="bg-cyan-600 hover:bg-cyan-700 text-white font-black py-4 px-8 rounded-2xl shadow-xl shadow-cyan-100 flex items-center transition-all transform hover:scale-105 uppercase tracking-[0.2em] text-[11px] leading-none">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Proses Kembali
        </button>
        @endcan
    </div>

    {{-- 2. CONTROL BAR --}}
    <div class="bg-white p-2 rounded-[2rem] shadow-sm border border-gray-100 flex flex-col lg:flex-row items-center justify-between gap-4">
        <div class="flex p-1 space-x-1 bg-gray-50 rounded-3xl w-full lg:w-auto overflow-x-auto">
            @foreach(['' => 'Semua Riwayat'] as $key => $label)
            <button wire:click="$set('filterStatus', '{{ $key }}')"
                class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all shadow-sm
                {{ $filterStatus === $key ? 'bg-white text-cyan-600 shadow-md' : 'text-gray-400 hover:bg-gray-100' }}">
                {{ $label }}
            </button>
            @endforeach
        </div>

        <div class="relative w-full lg:w-96 pr-2">
            <span class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input wire:model.live.debounce.300ms="search" type="text" 
                class="w-full h-12 pl-12 pr-6 rounded-2xl border-gray-100 bg-gray-50 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-xs transition-all placeholder:text-gray-300" 
                placeholder="Cari Kode atau Plat...">
        </div>
    </div>

    {{-- 3. DATA TABLE --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden mt-6">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left">
                <thead class="bg-gray-50/80 border-b border-gray-100">
                    <tr>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">ID Transaksi</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Pelanggan</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Unit Mobil</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em]">Tanggal Kembali</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-center">Status Denda</th>
                        <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pengembalian as $item)
                    <tr class="hover:bg-cyan-50/20 transition-colors group">
                        <td class="px-8 py-6">
                            <div class="font-black text-cyan-700 text-sm tracking-tight">{{ $item->kode_pengembalian }}</div>
                            {{-- 🔹 Teks ID DB dihapus dari sini --}}
                        </td>
                        <td class="px-8 py-6">
                            <div class="font-bold text-gray-800 text-xs">{{ $item->peminjaman->user->name ?? 'N/A' }}</div>
                            <div class="text-[10px] text-gray-400 mt-0.5">{{ $item->peminjaman->user->email ?? '-' }}</div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="font-bold text-gray-800 text-xs">{{ $item->peminjaman->mobil->merek ?? '?' }}</div>
                            <div class="text-[10px] font-mono text-cyan-600 font-bold mt-1">{{ $item->peminjaman->mobil->id ?? 'N/A' }}</div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-xs font-black text-gray-700">{{ \Carbon\Carbon::parse($item->tanggal_pengembalian)->format('d M Y') }}</div>
                        </td>
                        <td class="px-8 py-6 text-center">
                            @if($dendaList->has($item->peminjaman_id))
                                <span class="px-3 py-1 bg-rose-50 text-rose-600 text-[10px] font-black rounded-lg border border-rose-100 uppercase tracking-tighter">
                                    Ada Denda
                                </span>
                                <div class="text-[9px] text-rose-500 font-bold mt-1">Rp {{ number_format($dendaList[$item->peminjaman_id]->total_denda ?? 0, 0, ',', '.') }}</div>
                            @else
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-lg border border-emerald-100 uppercase tracking-widest leading-none">
                                    Aman (Clear)
                                </span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end gap-2">
                                {{-- 🔹 Passing Parameter Menggunakan String --}}
                                <button wire:click="showDetail('{{ $item->kode_pengembalian }}')" class="p-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all border border-cyan-100 shadow-sm" title="Detail">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                                
                                @can('delete-pengembalian')
                                {{-- 🔹 Passing Parameter Menggunakan String --}}
                                <button wire:confirm="Hapus data pengembalian? Status mobil akan kembali jadi 'Disewa'." wire:click="delete('{{ $item->kode_pengembalian }}')" class="p-2.5 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100 shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-24 text-center text-gray-400 text-sm font-medium italic">Belum ada riwayat pengembalian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-8 py-8 border-t border-gray-100 bg-gray-50/50">
            {{ $pengembalian->links('components.pagination-info') }}
        </div>
    </div>

    {{-- 4. MODAL CREATE / PROCESS RETURN --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
                <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'store' }}">
                    <div class="px-10 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">Penyelesaian Sewa</h3>
                            <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Formulir Pengecekan Armada Kembali</p>
                        </div>
                        <button type="button" wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        
                        {{-- Searchable Rental Selection (Alpine.js) --}}
                        @if(!$isEditMode)
                        <div class="group" x-data="{
                            open: false,
                            search: '',
                            selected: @entangle('peminjaman_id').live, 
                            rentals: {{ $active_rentals->map(fn($r) => ['id' => $r->id, 'text' => strtoupper($r->user->name).' - '.$r->mobil->id])->values()->toJson() }},
                            get filteredRentals() {
                                if (this.search === '') return this.rentals;
                                return this.rentals.filter(r => r.text.toLowerCase().includes(this.search.toLowerCase()));
                            },
                            get selectedText() {
                                let r = this.rentals.find(x => x.id == this.selected);
                                return r ? r.text : '-- Pilih Transaksi Aktif --';
                            }
                        }">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Pilih Booking Berjalan</label>
                            <div class="relative">
                                <button type="button" @click="open = !open" class="w-full h-14 rounded-2xl border bg-gray-50 px-5 text-left font-bold text-sm flex items-center justify-between transition-all focus:outline-none @error('peminjaman_id') border-rose-500 bg-rose-50 text-rose-900 focus:ring-4 focus:ring-rose-500/10 @else border-gray-100 text-gray-800 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                    <span x-text="selectedText"></span>
                                    <svg class="w-5 h-5 text-gray-400" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>

                                <div x-show="open" @click.away="open = false" class="absolute z-30 mt-2 w-full bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden" style="display:none;">
                                    <div class="p-3 border-b border-gray-50">
                                        <input x-model="search" type="text" class="w-full h-10 rounded-xl border-gray-100 bg-gray-50 px-4 text-xs font-bold text-gray-700 placeholder-gray-400 focus:border-cyan-500 transition-all" placeholder="Cari Pelanggan atau Plat Mobil...">
                                    </div>
                                    <div class="max-h-52 overflow-y-auto custom-scrollbar">
                                        <template x-for="r in filteredRentals" :key="r.id">
                                            <div @click="selected = r.id; open = false; search = ''" 
                                                class="px-5 py-4 cursor-pointer hover:bg-cyan-50 hover:text-cyan-700 transition-colors border-b border-gray-50 last:border-0 font-bold text-[11px] uppercase tracking-wider text-gray-600">
                                                <span x-text="r.text"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredRentals.length === 0" class="p-5 text-center text-[10px] font-bold text-gray-400 uppercase italic">Peminjaman tidak ditemukan.</div>
                                    </div>
                                </div>
                            </div>
                            @error('peminjaman_id') <span class="text-rose-500 text-[10px] font-black mt-2 block tracking-widest">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2 group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Tanggal Fisik Kembali</label>
                                <input wire:model.live="tanggal_pengembalian" type="date" class="w-full h-14 rounded-2xl px-5 focus:outline-none transition-all font-bold @error('tanggal_pengembalian') border border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50 text-gray-700 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                @error('tanggal_pengembalian') <span class="text-rose-500 text-[10px] font-black mt-2 block tracking-widest">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2 group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Status Kondisi Armada</label>
                                <textarea wire:model.live.debounce.500ms="kondisi_mobil_kembali" minlength="5" maxlength="1000" class="w-full h-32 rounded-[2rem] p-6 focus:outline-none transition-all font-bold custom-scrollbar @error('kondisi_mobil_kembali') border border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50 text-gray-700 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror" placeholder="Contoh: Mobil dalam kondisi bersih, tidak ada lecet baru."></textarea>
                                @error('kondisi_mobil_kembali') <span class="text-rose-500 text-[10px] font-black mt-2 block tracking-widest">{{ $message }}</span> @enderror
                            </div>

                            <div class="md:col-span-2 group">
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Catatan Opsional</label>
                                <input wire:model.live.debounce.500ms="catatan_pengembalian" maxlength="500" type="text" class="w-full h-14 rounded-2xl px-5 focus:outline-none transition-all font-bold @error('catatan_pengembalian') border border-rose-500 bg-rose-50 text-rose-900 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50 text-gray-700 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror" placeholder="Misal: Mobil kembali dengan aman tanpa kendala">
                                @error('catatan_pengembalian') <span class="text-rose-500 text-[10px] font-black mt-2 block tracking-widest">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex justify-center rounded-2xl px-12 py-4 bg-cyan-600 text-xs font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em] leading-none disabled:opacity-50">
                            <span wire:loading.remove>{{ $isEditMode ? 'Simpan Perubahan' : 'Selesaikan Transaksi' }}</span>
                            <span wire:loading>Memproses...</span>
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
    @if($showDetailModal && $selectedPengembalian)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full border border-gray-100">
                <div class="px-10 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">Resume Pengembalian</h3>
                        <p class="text-[10px] text-cyan-600 font-bold uppercase tracking-[0.25em] mt-1">{{ $selectedPengembalian->kode_pengembalian }}</p>
                    </div>
                    <button wire:click="closeModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="p-10 space-y-8">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Pelanggan</p>
                            <p class="font-black text-gray-800 text-sm uppercase">{{ $selectedPengembalian->peminjaman->user->name }}</p>
                        </div>
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Armada</p>
                            <p class="font-black text-gray-800 text-sm uppercase">{{ $selectedPengembalian->peminjaman->mobil->merek }}</p>
                            <p class="text-[10px] font-mono text-cyan-600 font-bold mt-1">{{ $selectedPengembalian->peminjaman->mobil->id }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h5 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] border-b pb-2">Status Kondisi Akhir</h5>
                        <div class="p-6 bg-cyan-50/50 rounded-[2rem] border border-cyan-100 text-cyan-900 font-bold text-xs leading-relaxed italic">
                            "{{ $selectedPengembalian->kondisi_mobil }}"
                        </div>
                    </div>

                    {{-- Menampilkan informasi dari tabel Denda jika data tersebut exist --}}
                    @if($selectedDenda)
                    <div class="p-6 bg-rose-50 rounded-[2rem] border border-rose-100 flex justify-between items-center shadow-inner mt-4">
                        <div>
                            <p class="text-xs font-black text-rose-500 uppercase tracking-widest">Denda Ditetapkan</p>
                            <p class="text-[10px] font-black text-rose-400 mt-1.5 uppercase bg-white px-2 py-0.5 rounded border border-rose-100 inline-block">{{ str_replace('_', ' ', $selectedDenda->status) }}</p>
                        </div>
                        <p class="text-xl font-black text-rose-600">Rp {{ number_format($selectedDenda->total_denda ?? 0, 0, ',', '.') }}</p>
                    </div>
                    @endif
                </div>

                <div class="bg-gray-50/80 px-10 py-8 text-right border-t border-gray-100">
                    <button wire:click="closeModal" class="px-10 py-4 bg-cyan-600 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-cyan-700 transition-all shadow-xl">Tutup Resume</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <x-toast-notification />
</div>