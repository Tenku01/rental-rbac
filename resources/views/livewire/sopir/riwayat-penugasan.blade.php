<div class="container mx-auto p-4 sm:p-6 lg:p-8 font-inter">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
    </style>

    @if($viewMode === 'index')
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Riwayat Penugasan</h1>
            <p class="text-sm font-medium text-gray-500 mt-2">Daftar seluruh tugas yang telah Anda selesaikan dengan baik.</p>
        </div>

        {{-- Tabel Riwayat --}}
        <div class="bg-white shadow-sm rounded-[2rem] overflow-hidden border border-gray-100">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <th class="p-6">Tanggal</th>
                            <th class="p-6">Mobil</th>
                            <th class="p-6">Pelanggan</th>
                            <th class="p-6">Status Akhir</th>
                            <th class="p-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($riwayatTugas as $tugas)
                            <tr class="hover:bg-emerald-50/30 transition-colors group">
                                <td class="p-6">
                                    <p class="text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($tugas->tanggal_sewa)->format('d M Y') }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">s/d {{ \Carbon\Carbon::parse($tugas->tanggal_kembali)->format('d M Y') }}</p>
                                </td>
                                <td class="p-6">
                                    <p class="text-sm font-bold text-gray-900">{{ $tugas->mobil->merek ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $tugas->mobil_id }}</p>
                                </td>
                                <td class="p-6">
                                    <p class="text-sm font-bold text-cyan-600">{{ $tugas->user->name ?? 'N/A' }}</p>
                                </td>
                                <td class="p-6">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600">
                                        {{ $tugas->status }}
                                    </span>
                                </td>
                                <td class="p-6 text-right">
                                    <button wire:click="viewDetail({{ $tugas->id }})" class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-200 rounded-xl text-xs font-bold text-gray-700 shadow-sm hover:bg-gray-50 hover:text-emerald-600 transition-colors">
                                        Lihat Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-16 text-center">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 text-gray-400 mb-4">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500">Belum ada riwayat penugasan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($viewMode === 'detail' && $selectedTask)
        {{-- Tombol Kembali --}}
        <button wire:click="backToIndex" class="group flex items-center text-[11px] font-black uppercase tracking-widest text-emerald-600 hover:text-emerald-800 mb-8 transition-colors">
            <div class="h-8 w-8 bg-emerald-50 rounded-full flex items-center justify-center mr-3 group-hover:bg-emerald-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </div>
            Kembali ke Daftar Riwayat
        </button>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- KOLOM KIRI: Detail & Logbook --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Card Info --}}
                <div class="bg-white shadow-sm rounded-[2rem] p-8 border border-gray-100">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-black text-gray-900 tracking-tight">{{ $selectedTask->mobil->merek ?? 'N/A' }} ({{ $selectedTask->mobil_id }})</h2>
                            <p class="text-sm font-medium text-gray-500 mt-1">Pelanggan: <span class="text-cyan-600 font-bold">{{ $selectedTask->user->name ?? 'N/A' }}</span></p>
                        </div>
                        <span class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100">
                            {{ $selectedTask->status }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 bg-gray-50 rounded-2xl p-5">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal Mulai</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">{{ \Carbon\Carbon::parse($selectedTask->tanggal_sewa)->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Tanggal Selesai</p>
                            <p class="text-sm font-bold text-gray-900 mt-1">{{ \Carbon\Carbon::parse($selectedTask->tanggal_kembali)->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Riwayat Logbook --}}
                <div class="bg-white shadow-sm rounded-[2rem] p-8 border border-gray-100">
                    <h3 class="text-lg font-black text-gray-900 uppercase tracking-tight mb-8">Riwayat Logbook</h3>
                    
                    @if(count($logHistory) === 0)
                        <p class="text-sm text-gray-500 text-center py-4">Tidak ada catatan logbook untuk tugas ini.</p>
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
                                                <div class="relative">
                                                    @php
                                                        $iconBg = match($log->status_log) {
                                                            'selesai_peminjaman', 'selesai tugas' => 'bg-rose-50 text-rose-500 ring-rose-100',
                                                            'mulai_kerja' => 'bg-cyan-50 text-cyan-500 ring-cyan-100',
                                                            'selesai_hari_ini' => 'bg-purple-50 text-purple-500 ring-purple-100',
                                                            default => 'bg-emerald-50 text-emerald-500 ring-emerald-100'
                                                        };
                                                        $iconChar = match($log->status_log) {
                                                            'selesai_peminjaman', 'selesai tugas' => '🏁',
                                                            'mulai_kerja' => '🚀',
                                                            'selesai_hari_ini' => '🏠',
                                                            default => '📍'
                                                        };
                                                    @endphp
                                                    <span class="h-10 w-10 rounded-2xl flex items-center justify-center ring-4 {{ $iconBg }} text-lg shadow-sm">
                                                        {{ $iconChar }}
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 bg-gray-50/50 border border-gray-100 rounded-2xl p-4">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <h4 class="text-xs font-black text-gray-900 uppercase">{{ str_replace('_', ' ', $log->status_log) }}</h4>
                                                            <p class="text-[10px] font-bold text-gray-400 mt-0.5">
                                                                {{ \Carbon\Carbon::parse($log->waktu_log ?? $log->created_at)->format('d M Y - H:i') }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <p class="text-xs text-gray-600 font-medium mt-2">
                                                        {{ $log->deskripsi_aktivitas }}
                                                    </p>
                                                    @if($log->foto_bukti)
                                                        <a href="{{ Storage::url($log->foto_bukti) }}" target="_blank" class="inline-flex items-center mt-3 text-[10px] font-bold text-cyan-600 hover:underline">
                                                            📸 Lihat Lampiran Foto
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

            {{-- KOLOM KANAN: Riwayat Chat --}}
            <div class="lg:col-span-1">
                <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 flex flex-col h-[600px] overflow-hidden sticky top-8">
                    <div class="px-6 py-5 border-b border-gray-50 bg-gray-50/50">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight flex items-center">
                            <svg class="w-4 h-4 mr-2 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            Riwayat Chat
                        </h3>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50/30">
                        @if(count($chatHistory) === 0)
                            <div class="h-full flex flex-col items-center justify-center text-center opacity-50">
                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                <p class="text-xs font-bold text-gray-500">Tidak ada percakapan<br>selama penugasan ini.</p>
                            </div>
                        @else
                            @foreach($chatHistory as $pesan)
                                @php
                                    $isMe = $pesan->pengirim_id === Auth::id();
                                @endphp
                                <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                                    <div class="max-w-[85%] rounded-2xl px-4 py-2.5 text-sm shadow-sm {{ $isMe ? 'bg-cyan-600 text-white rounded-br-none' : 'bg-white border border-gray-100 text-gray-800 rounded-bl-none' }}">
                                        {{ $pesan->isi_pesan }}
                                    </div>
                                    <span class="text-[9px] font-bold text-gray-400 mt-1 px-1">
                                        {{ $isMe ? 'Anda' : ($pesan->pengirim->name ?? 'Pelanggan') }} • {{ \Carbon\Carbon::parse($pesan->created_at)->format('H:i') }}
                                    </span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>