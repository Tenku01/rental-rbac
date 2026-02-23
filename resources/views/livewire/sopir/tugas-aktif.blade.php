<div class="container mx-auto p-4 sm:p-6 lg:p-8 font-inter">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
    </style>

    {{-- ========================================== --}}
    {{-- MODE 1: DAFTAR TUGAS AKTIF (INDEX)         --}}
    {{-- ========================================== --}}
    @if($viewMode === 'index')
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Logbook & Tugas Aktif</h1>
            <p class="text-sm font-medium text-gray-500 mt-2">Pilih tugas yang sedang berjalan untuk mencatat aktivitas rill Anda.</p>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-cyan-100 flex items-center justify-between hover:border-cyan-300 transition-all">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Total Tugas Aktif</p>
                    <p class="text-4xl font-black text-cyan-700 mt-2">{{ $stats['total'] }}</p>
                </div>
                <div class="h-14 w-14 bg-cyan-50 rounded-2xl flex items-center justify-center text-cyan-600">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-emerald-100 flex items-center justify-between hover:border-emerald-300 transition-all">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Dicatat Hari Ini</p>
                    <p class="text-4xl font-black text-emerald-600 mt-2">{{ $stats['sudah_hari_ini'] }}</p>
                </div>
                <div class="h-14 w-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-amber-100 flex items-center justify-between hover:border-amber-300 transition-all">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400">Belum Dicatat</p>
                    <p class="text-4xl font-black text-amber-600 mt-2">{{ $stats['belum_hari_ini'] }}</p>
                </div>
                <div class="h-14 w-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        {{-- Daftar Tugas --}}
        <div class="bg-white shadow-sm rounded-[2rem] overflow-hidden border border-gray-100">
            <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/50">
                <h2 class="text-lg font-black text-gray-900 uppercase tracking-tight">Daftar Penugasan</h2>
            </div>

            @if($tasks->isEmpty())
                <div class="text-center py-16 flex flex-col items-center">
                    <div class="h-20 w-20 bg-gray-50 rounded-full flex items-center justify-center mb-4 text-gray-400">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">Tidak ada tugas aktif</h3>
                    <p class="text-sm text-gray-500 font-medium">Berdiam diri saja? Tunggu penugasan selanjutnya.</p>
                </div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($tasks as $task)
                        @php
                            $sudahCatat = $task->logbooks->count() > 0;
                        @endphp
                        <button wire:click="openLogbookForm({{ $task->id }})" class="w-full text-left group hover:bg-cyan-50/30 transition-all duration-200">
                            <div class="p-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
                                
                                <div class="flex items-center gap-5">
                                    <div class="h-16 w-16 bg-cyan-100 rounded-2xl flex items-center justify-center text-cyan-600 group-hover:scale-110 transition-transform">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-black text-gray-900 tracking-tight">
                                            {{ $task->mobil->merek ?? 'N/A' }} - {{ $task->mobil->plat_nomor ?? 'N/A' }}
                                        </h3>
                                        <p class="text-sm font-bold text-gray-500 mt-1 uppercase tracking-widest text-[10px]">
                                            Pelanggan: <span class="text-cyan-600">{{ $task->user->pelanggan->nama_lengkap ?? 'N/A' }}</span>
                                        </p>
                                        <div class="flex items-center gap-4 mt-3">
                                            <span class="flex items-center text-xs font-semibold text-gray-500">
                                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                {{ \Carbon\Carbon::parse($task->tanggal_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($task->tanggal_selesai)->format('d M') }}
                                            </span>
                                            <span class="flex items-center text-xs font-semibold text-gray-500">
                                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                {{ $task->jumlah_penumpang ?? 1 }} Pax
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    @if($sudahCatat)
                                        <span class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span> Updated
                                        </span>
                                    @else
                                        <span class="px-4 py-2 bg-amber-50 text-amber-600 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 mr-2 animate-pulse"></span> Draft
                                        </span>
                                    @endif

                                    <div class="h-10 px-6 bg-cyan-600 text-white rounded-xl text-[11px] font-black uppercase tracking-widest flex items-center shadow-lg shadow-cyan-100 group-hover:bg-cyan-700 transition-colors">
                                        Isi Logbook
                                    </div>
                                </div>

                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- ========================================== --}}
    {{-- MODE 2: FORM PENGISIAN & RIWAYAT           --}}
    {{-- ========================================== --}}
    @if($viewMode === 'form' && $selectedTask)
        
        {{-- Tombol Kembali --}}
        <button wire:click="backToIndex" class="group flex items-center text-[11px] font-black uppercase tracking-widest text-cyan-600 hover:text-cyan-800 mb-8 transition-colors">
            <div class="h-8 w-8 bg-cyan-50 rounded-full flex items-center justify-center mr-3 group-hover:bg-cyan-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </div>
            Kembali ke Daftar Tugas
        </button>

        {{-- Info Banner --}}
        <div class="bg-cyan-900 rounded-[2rem] p-8 text-white shadow-xl mb-8 flex flex-col md:flex-row justify-between md:items-center gap-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 opacity-10">
                <svg class="w-48 h-48 -mr-10 -mt-10" fill="currentColor" viewBox="0 0 24 24"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </div>
            <div class="relative z-10">
                <p class="text-[10px] font-black text-cyan-400 uppercase tracking-[0.2em]">Target Kendaraan</p>
                <h1 class="text-3xl font-black tracking-tight mt-1">{{ $selectedTask->mobil->merek ?? 'N/A' }} ({{ $selectedTask->mobil->plat_nomor ?? 'N/A' }})</h1>
                <p class="text-sm font-medium text-cyan-100 mt-2">Pelanggan: {{ $selectedTask->user->pelanggan->nama_lengkap ?? 'N/A' }} | Lokasi: {{ $selectedTask->lokasi_jemput ?? 'Pool' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- KOLOM KIRI: Form Input --}}
            <div class="lg:col-span-1">
                <div class="bg-white shadow-sm rounded-[2rem] p-8 border border-gray-100 sticky top-8">
                    <h2 class="text-lg font-black text-gray-900 uppercase tracking-tight mb-6 flex items-center">
                        <span class="h-8 w-8 bg-cyan-50 rounded-xl flex items-center justify-center text-cyan-600 mr-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </span>
                        Catat Update
                    </h2>
                    
                    <form wire:submit.prevent="saveLog" class="space-y-5">
                            
                            {{-- Status --}}
                            <div>
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Status Aktivitas</label>
                                <select wire:model.live="status_log" required class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/20 text-sm font-semibold transition-all p-3">
                                    <option value="" disabled>-- Pilih Status --</option>
                                    <option value="mulai_kerja">🚀 Mulai Perjalanan</option>
                                    <option value="dalam_perjalanan">📍 Tiba di Titik Tengah / Update</option>
                                    <option value="selesai_hari_ini">🏠 Selesai Shift Hari Ini</option>
                                    <option value="selesai_peminjaman" class="text-rose-600 font-bold">🏁 Selesai Tugas (Kembali Pool)</option>
                                </select>
                                @error('status_log') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Deskripsi --}}
                            <div>
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Deskripsi / Laporan</label>
                                <textarea wire:model="deskripsi_aktivitas" rows="4" required placeholder="Cth: KM Awal 2500, bensin full. Penumpang sudah masuk..." class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/20 text-sm font-medium transition-all p-3"></textarea>
                                @error('deskripsi_aktivitas') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Foto --}}
                            <div>
                                <label class="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-2">Foto Pendukung (Opsional)</label>
                                <div class="relative mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-2xl hover:border-cyan-400 hover:bg-cyan-50/50 transition-all overflow-hidden group">
                                    @if ($foto_bukti)
                                        <img src="{{ $foto_bukti->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-30 group-hover:opacity-10 transition-opacity">
                                    @endif
                                    <div class="space-y-2 text-center relative z-10">
                                        <svg class="mx-auto h-10 w-10 text-gray-400 group-hover:text-cyan-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label class="relative cursor-pointer rounded-md font-bold text-cyan-600 hover:text-cyan-700">
                                                <span>{{ $foto_bukti ? 'Ganti Foto' : 'Pilih Foto' }}</span>
                                                <input type="file" wire:model="foto_bukti" class="sr-only" accept="image/*">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div wire:loading wire:target="foto_bukti" class="text-[10px] font-bold text-cyan-600 mt-2 animate-pulse">Mengunggah foto...</div>
                                @error('foto_bukti') <span class="text-xs text-rose-500 font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <button type="submit" class="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-black uppercase tracking-widest text-[11px] py-4 px-4 rounded-xl shadow-lg shadow-cyan-200 transition-all flex items-center justify-center relative overflow-hidden" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="saveLog">Simpan Logbook</span>
                                <span wire:loading wire:target="saveLog" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Memproses...
                                </span>
                            </button>

                        </form>
                </div>
            </div>

            {{-- KOLOM KANAN: Riwayat Timeline --}}
            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm rounded-[2rem] p-8 border border-gray-100">
                    <div class="flex justify-between items-center mb-8 border-b border-gray-50 pb-4">
                        <h2 class="text-lg font-black text-gray-900 uppercase tracking-tight">Timeline Perjalanan</h2>
                        <span class="bg-gray-100 text-gray-600 text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-full">
                            {{ count($logHistory) }} Entri
                        </span>
                    </div>

                    @if(count($logHistory) === 0)
                        <div class="text-center py-12">
                            <p class="text-gray-400 font-bold text-sm">Belum ada catatan untuk tugas ini.</p>
                        </div>
                    @else
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @foreach($logHistory as $log)
                                    <li>
                                        <div class="relative pb-8">
                                            @if (!$loop->last)
                                                <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex items-start space-x-4">
                                                
                                                {{-- Ikon Status --}}
                                                <div class="relative">
                                                    @php
                                                        $iconBg = match($log->status_log) {
                                                            'selesai_peminjaman' => 'bg-rose-50 text-rose-500 ring-rose-100',
                                                            'mulai_kerja' => 'bg-cyan-50 text-cyan-500 ring-cyan-100',
                                                            'selesai_hari_ini' => 'bg-purple-50 text-purple-500 ring-purple-100',
                                                            default => 'bg-emerald-50 text-emerald-500 ring-emerald-100'
                                                        };
                                                        $iconChar = match($log->status_log) {
                                                            'selesai_peminjaman' => '🏁',
                                                            'mulai_kerja' => '🚀',
                                                            'selesai_hari_ini' => '🏠',
                                                            default => '📍'
                                                        };
                                                    @endphp
                                                    <span class="h-10 w-10 rounded-2xl flex items-center justify-center ring-4 {{ $iconBg }} text-lg shadow-sm">
                                                        {{ $iconChar }}
                                                    </span>
                                                </div>
                                                
                                                {{-- Konten Log --}}
                                                <div class="min-w-0 flex-1 bg-gray-50/50 hover:bg-gray-50 border border-gray-100 rounded-2xl p-5 transition-colors">
                                                    <div class="flex justify-between items-start mb-2">
                                                        <div>
                                                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">
                                                                {{ str_replace('_', ' ', $log->status_log) }}
                                                            </h3>
                                                            <p class="text-[10px] font-bold text-gray-400 mt-0.5 uppercase tracking-widest">
                                                                @php $waktu = \Carbon\Carbon::parse($log->waktu_log ?? $log->created_at); @endphp
                                                                {{ $waktu->format('d M Y - H:i') }} ({{ $waktu->diffForHumans() }})
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <p class="text-sm text-gray-600 font-medium leading-relaxed mt-3">
                                                        {{ $log->deskripsi_aktivitas }}
                                                    </p>
                                                    
                                                    @if($log->foto_bukti)
                                                        <a href="{{ Storage::url($log->foto_bukti) }}" target="_blank" class="inline-flex items-center mt-4 text-[10px] font-black text-cyan-600 bg-cyan-50 px-3 py-1.5 rounded-lg hover:bg-cyan-100 uppercase tracking-widest transition-colors">
                                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                            Lihat Lampiran Foto
                                                        </a>
                                                    @endif
                                                </div>

                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <x-toast-notification />
</div>