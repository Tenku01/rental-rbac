<div class="p-8 space-y-10 bg-[#f9fafb] min-h-screen font-inter">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #0891b2; }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-down { animation: fadeInDown 0.3s ease-out; }

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

    <!-- TOAST NOTIFICATION -->
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
            <div class="h-14 w-14 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-cyan-100 ring-4 ring-cyan-50">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight leading-none uppercase ">Pengecekan Armada</h1>
                <p class="text-sm text-gray-500 font-medium mt-2">Inspeksi awal sebelum penyerahan dan inspeksi pasca-pengembalian.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-10">
        
        {{-- SECTION A1: INSPEKSI SEBELUM PENYERAHAN (PRE-RENTAL) --}}
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="h-6 w-1.5 bg-blue-500 rounded-full"></div>
                <h2 class="text-sm font-black text-gray-800 uppercase tracking-[0.2em]">Menunggu Penyerahan (Awal)</h2>
            </div>
            
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-blue-50/30 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black text-blue-600 uppercase tracking-widest">ID Pesanan</th>
                                <th class="px-6 py-4 text-[10px] font-black text-blue-600 uppercase tracking-widest">Detail Pelanggan</th>
                                <th class="px-6 py-4 text-[10px] font-black text-blue-600 uppercase tracking-widest text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($pendingDepartures as $pre)
                            <tr wire:key="pre-{{ $pre->id }}" class="hover:bg-blue-50/10 transition-colors">
                                <td class="px-6 py-5">
                                    <div class="font-black text-xs text-gray-800">#{{ $pre->id }}</div>
                                    <div class="text-[9px] text-gray-400 font-bold mt-1 uppercase">{{ \Carbon\Carbon::parse($pre->tanggal_sewa)->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="font-bold text-gray-800 text-xs uppercase">{{ $pre->user->name ?? 'N/A' }}</div>
                                    <div class="font-bold text-cyan-600 text-[10px] mt-0.5">{{ $pre->mobil->merek ?? 'N/A' }} [{{ $pre->mobil->id ?? 'N/A' }}]</div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    @can('create-vehicle_inspections')
                                        @if(empty($pre->kondisi_mobil))
                                        <button wire:click="openPreInspection({{ $pre->id }})" class="px-5 py-2 bg-blue-500 hover:bg-blue-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-blue-100 active:scale-95 whitespace-nowrap">
                                            Catat Awal
                                        </button>
                                        @else
                                        <button wire:click="openPreInspection({{ $pre->id }})" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-amber-100 active:scale-95 whitespace-nowrap">
                                            Edit Catatan
                                        </button>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-gray-400 text-[10px] font-bold uppercase tracking-widest italic">Tidak ada armada yang menunggu penyerahan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- SECTION A2: INSPEKSI PASCA PENGEMBALIAN (POST-RENTAL) --}}
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="h-6 w-1.5 bg-amber-500 rounded-full"></div>
                <h2 class="text-sm font-black text-gray-800 uppercase tracking-[0.2em]">Menunggu Pengembalian (Akhir)</h2>
            </div>
            
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-amber-50/30 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-[10px] font-black text-amber-600 uppercase tracking-widest">ID Kembali</th>
                                <th class="px-6 py-4 text-[10px] font-black text-amber-600 uppercase tracking-widest">Detail Pelanggan</th>
                                <th class="px-6 py-4 text-[10px] font-black text-amber-600 uppercase tracking-widest text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($pendingReturns as $p)
                            <!-- 🔹 PERBAIKAN: Gunakan $p->kode_pengembalian -->
                            <tr wire:key="ret-{{ $p->kode_pengembalian }}" class="hover:bg-amber-50/10 transition-colors">
                                <td class="px-6 py-5">
                                    <div class="font-black text-xs text-gray-800">{{ $p->kode_pengembalian }}</div>
                                    <div class="text-[9px] text-gray-400 font-bold mt-1 uppercase">{{ \Carbon\Carbon::parse($p->tanggal_pengembalian)->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="font-bold text-gray-800 text-xs uppercase">{{ $p->peminjaman->user->name ?? 'N/A' }}</div>
                                    <div class="font-bold text-cyan-600 text-[10px] mt-0.5">{{ $p->peminjaman->mobil->merek ?? 'N/A' }} [{{ $p->peminjaman->mobil->id ?? 'N/A' }}]</div>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    @can('create-vehicle_inspections')
                                    <!-- 🔹 PERBAIKAN: Lempar string kode_pengembalian diapit tanda kutip -->
                                    <button wire:click="createInspection('{{ $p->kode_pengembalian }}')" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-amber-100 active:scale-95 whitespace-nowrap">
                                        Inspeksi Akhir
                                    </button>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-gray-400 text-[10px] font-bold uppercase tracking-widest italic">Antrean pengecekan akhir kosong.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- SECTION B: RIWAYAT INSPEKSI KESELURUHAN --}}
    <div class="pt-10 space-y-6">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-3">
                <div class="h-6 w-1.5 bg-cyan-600 rounded-full"></div>
                <h2 class="text-sm font-black text-gray-800 uppercase tracking-[0.2em]">Log Riwayat Inspeksi Akhir</h2>
            </div>
            
            <div class="relative w-80">
                <input wire:model.live.debounce.300ms="search" type="text" class="w-full h-11 pl-11 pr-4 rounded-xl border border-gray-100 bg-white shadow-sm focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-bold text-xs transition-all focus:outline-none" placeholder="Cari Plat Nomor Armada...">
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
                            <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-center">Kondisi Akhir</th>
                            <th class="px-8 py-5 text-[11px] font-extrabold text-gray-400 uppercase tracking-[0.15em] text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($inspections as $item)
                        <tr wire:key="ins-{{ $item->id }}" class="hover:bg-cyan-50/20 transition-colors group">
                            <td class="px-8 py-6 font-bold text-gray-800 text-xs">{{ $item->pemeriksa->name ?? 'Staff' }}</td>
                            <td class="px-8 py-6 font-black text-gray-900 text-xs">
                                {{ $item->mobil->merek ?? '?' }} 
                                <span class="text-cyan-600 font-mono ml-1">[{{ $item->mobil->id ?? 'N/A' }}]</span>
                            </td>
                            <td class="px-8 py-6 text-xs font-bold text-gray-500">{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y H:i') }}</td>
                            <td class="px-8 py-6 text-center">
                                @php
                                    $badge = match($item->kondisi) {
                                        'Baik Sempurna' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'Rusak Berat' => 'bg-rose-50 text-rose-600 border-rose-100',
                                        'Perlu Perbaikan Ringan' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        default => 'bg-gray-50 text-gray-500'
                                    };
                                @endphp
                                <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest border {{ $badge }}">
                                    {{ $item->kondisi }}
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-3">
                                    <button wire:click="showDetail({{ $item->id }})" class="p-2.5 text-cyan-600 bg-cyan-50 hover:bg-cyan-600 hover:text-white rounded-xl transition-all border border-cyan-100 shadow-sm" title="Lihat Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>
                                    @can('delete-vehicle_inspections')
                                    <button onclick="confirm('Hapus data riwayat inspeksi ini secara permanen?') || event.stopImmediatePropagation()" wire:click="delete({{ $item->id }})" class="p-2.5 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all border border-rose-100 shadow-sm" title="Hapus Riwayat">
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


    {{-- MODAL 1: PENGECEKAN AWAL (PRE-RENTAL) --}}
    @if($showPreModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closePreModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-gray-100">
                <form wire:submit.prevent="storePreInspection">
                    <div class="px-10 py-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <div>
                            <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight">Pengecekan Awal Kendaraan</h3>
                            <p class="text-[10px] text-blue-600 font-bold uppercase tracking-[0.25em] mt-1 leading-none">Inspeksi Sebelum Penyerahan ke Pelanggan</p>
                        </div>
                        <button type="button" wire:click="closePreModal" class="h-10 w-10 flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 rounded-2xl transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Penyewa</p>
                                <p class="font-bold text-gray-800 text-sm uppercase">{{ $pre_infoPenyewa ?? '-' }}</p>
                            </div>
                            <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Armada</p>
                                <p class="font-bold text-gray-800 text-sm uppercase">{{ $pre_infoMobil ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="group">
                            <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">
                                Catatan Kondisi Kendaraan (Awal) <span class="text-rose-500">*</span>
                            </label>
                            <textarea wire:model.live.debounce.300ms="pre_kondisi" 
                                class="w-full h-32 rounded-[2rem] p-6 font-bold text-gray-700 text-sm transition-all focus:outline-none placeholder:text-gray-300 border border-gray-100 bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" 
                                placeholder="Jelaskan kondisi bodi mobil, bensin, dan interior..."></textarea>
                            <p class="text-[10px] text-gray-400 font-bold mt-2 ml-2 italic">*Catatan yang dibuat oleh pelanggan tidak akan bisa dihapus/diedit di sini.</p>
                            @error('pre_kondisi') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                        <button type="submit" wire:loading.attr="disabled"
                            class="relative inline-flex justify-center items-center rounded-2xl px-12 py-4 bg-blue-600 text-xs font-black text-white hover:bg-blue-700 shadow-xl shadow-blue-100 transition-all uppercase tracking-[0.2em] leading-none disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="storePreInspection">Selesaikan Inspeksi Awal</span>
                            <span wire:loading wire:target="storePreInspection" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                        <button type="button" wire:click="closePreModal" class="inline-flex justify-center rounded-2xl px-8 py-4 bg-white text-xs font-black text-gray-400 hover:bg-gray-100 border border-gray-200 transition-all uppercase tracking-[0.2em] leading-none">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif


    {{-- MODAL 2: PENGECEKAN AKHIR (POST-RENTAL) --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-md transition-opacity" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full border border-gray-100">
                <form wire:submit.prevent="store" x-data="{ 
                    lateFine: @entangle('lateFine'), 
                    damageCost: @entangle('biaya_kerusakan').live, 
                    get totalFine() { return (Number(this.lateFine) || 0) + (Number(this.damageCost) || 0) } 
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
                                <p class="font-bold text-gray-800 text-sm uppercase">{{ $infoPenyewa ?? '-' }}</p>
                            </div>
                            <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Armada</p>
                                <p class="font-bold text-gray-800 text-sm uppercase">{{ $infoMobil ?? '-' }}</p>
                            </div>
                            <div class="bg-indigo-50 p-5 rounded-2xl border border-indigo-100">
                                <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Harga Sewa / Hari</p>
                                <p class="font-black text-indigo-700 text-sm uppercase">Rp {{ number_format((float)($hargaPerHari ?? 0), 0, ',', '.') }}</p>
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
                                    <p class="text-2xl font-black text-rose-600">Rp {{ number_format((float)($lateFine ?? 0), 0, ',', '.') }}</p>
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
                            <div class="space-y-6 animate-fade-in-down border-t border-dashed pt-6 border-gray-200">
                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Deskripsi Kerusakan <span class="text-rose-500">*</span></label>
                                    <textarea wire:model.live.debounce.300ms="deskripsi_kerusakan" 
                                        class="w-full h-24 rounded-2xl p-4 font-bold text-gray-700 text-sm transition-all focus:outline-none placeholder:text-gray-300
                                        @error('deskripsi_kerusakan') border-rose-500 bg-rose-50 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-white focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror" 
                                        placeholder="Contoh: Bumper depan penyok, spion kiri patah..."></textarea>
                                    @error('deskripsi_kerusakan') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ $message }}</span> @enderror
                                </div>
                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Estimasi Biaya Perbaikan (Rp) <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 font-black">Rp</div>
                                        <input wire:model.live.debounce.300ms="biaya_kerusakan" type="number" min="0" step="1000"
                                            class="w-full h-14 pl-12 rounded-2xl px-5 font-black text-lg transition-all focus:outline-none shadow-inner placeholder:text-gray-300
                                            @error('biaya_kerusakan') border-rose-500 bg-rose-50 text-rose-900 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-white text-gray-800 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror">
                                    </div>
                                    @error('biaya_kerusakan') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ $message }}</span> @enderror
                                </div>
                            </div>
                            @else
                            <p class="text-xs font-bold text-gray-400 italic">Klik toggle di samping jika ditemukan kerusakan fisik baru.</p>
                            @endif
                        </div>

                        {{-- 4. HASIL INSPEKSI STAFF --}}
                        <div class="space-y-6 pt-4 border-t border-dashed border-gray-200">
                            <h4 class="text-[11px] font-black text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                3. Keputusan Kelayakan Armada
                            </h4>
                            <div class="space-y-6">
                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Kondisi Umum Mobil <span class="text-rose-500">*</span></label>
                                    <select wire:model.live="kondisi" class="w-full h-14 rounded-2xl border border-gray-100 bg-gray-50 px-5 focus:outline-none focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 font-black text-xs uppercase tracking-widest transition-all cursor-pointer">
                                        <option value="Baik Sempurna">BAIK SEMPURNA (READY)</option>
                                        <option value="Perlu Perbaikan Ringan">PERLU PERBAIKAN RINGAN</option>
                                        <option value="Rusak Berat">RUSAK BERAT (WORKSHOP)</option>
                                    </select>
                                    @error('kondisi') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="group">
                                    <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-3">Catatan Audit Internal <span class="text-rose-500">*</span></label>
                                    <textarea wire:model.live.debounce.300ms="notes" 
                                        class="w-full h-24 rounded-2xl p-5 font-bold text-sm transition-all focus:outline-none placeholder:text-gray-400
                                        @error('notes') border-rose-500 bg-rose-50 text-rose-900 focus:ring-4 focus:ring-rose-500/10 @else border border-gray-100 bg-gray-50 text-gray-700 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 @enderror" 
                                        placeholder="Misal: Bensin sudah diisi full kembali oleh penyewa."></textarea>
                                    @error('notes') <span class="text-rose-500 text-[10px] font-black uppercase mt-2 ml-1 flex items-center gap-1 tracking-widest"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- 5. TOTAL BIAYA STICKY --}}
                        <div class="bg-gray-900 rounded-[2rem] p-8 text-white flex justify-between items-center shadow-2xl relative overflow-hidden mt-6">
                            <div class="absolute -right-10 -top-10 h-32 w-32 bg-cyan-500/10 rounded-full blur-3xl"></div>
                            <div>
                                <span class="text-[10px] font-black text-cyan-400 uppercase tracking-[0.4em] block mb-2">TOTAL AKHIR DENDA</span>
                                <span class="text-[8px] text-gray-400 uppercase tracking-widest">(Keterlambatan + Kerusakan)</span>
                            </div>
                            <div class="text-4xl font-black tracking-tighter">
                                Rp <span x-text="(Number(totalFine) || 0).toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                    </div>

                    <div class="bg-gray-50/80 px-10 py-8 flex flex-row-reverse gap-4 border-t border-gray-100 rounded-b-[2.5rem]">
                        <button type="submit" wire:loading.attr="disabled"
                            class="relative inline-flex justify-center items-center rounded-2xl px-12 py-4 bg-cyan-600 text-xs font-black text-white hover:bg-cyan-700 shadow-xl shadow-cyan-100 transition-all uppercase tracking-[0.2em] leading-none disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="store">Simpan Hasil Audit</span>
                            <span wire:loading wire:target="store" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
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

    {{-- MODAL 3: DETAIL AUDIT --}}
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
                            <p class="font-black text-gray-800 text-xs uppercase">{{ $selectedInspection->kondisi }}</p>
                        </div>
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 text-center">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Diaudit Oleh</p>
                            <p class="font-black text-gray-800 text-xs uppercase">{{ $selectedInspection->pemeriksa->name ?? 'Sistem' }}</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h5 class="text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] border-b pb-2">Catatan Temuan</h5>
                        <div class="p-6 bg-cyan-50/50 rounded-[2rem] border border-cyan-100 text-cyan-900 font-bold text-xs leading-relaxed italic">
                            "{{ $selectedInspection->keterangan }}"
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50/80 px-10 py-8 text-right border-t border-gray-100">
                    <button wire:click="closeModal" class="px-10 py-4 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-gray-800 transition-all active:scale-95 shadow-md">Tutup Audit</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>