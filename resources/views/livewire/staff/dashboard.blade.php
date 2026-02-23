<div class="p-8 space-y-10 bg-[#f9fafb] min-h-screen font-inter">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        .font-inter { font-family: 'Inter', sans-serif; }
    </style>

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-gray-200 pb-8">
        <div class="flex items-center gap-5">
            <div class="h-16 w-16 bg-cyan-600 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-cyan-100 ring-4 ring-cyan-50">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Selamat Datang, {{ Auth::user()->name }}!</h1>
                <p class="text-sm font-bold text-gray-500 mt-2 uppercase tracking-widest">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>
    </div>

    {{-- 2. KARTU METRIK DINAMIS --}}
    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
        @foreach ($metrics as $metric)
            <div class="bg-white overflow-hidden shadow-sm rounded-[2.5rem] transition duration-300 hover:shadow-xl p-8 border-b-[6px] {{ $metric['color'] === 'yellow' ? 'border-amber-400 hover:border-amber-500' : 'border-emerald-500 hover:border-emerald-600' }}">
                <div class="flex items-center">
                    <div class="flex-shrink-0 {{ $metric['color'] === 'yellow' ? 'bg-amber-50 text-amber-500' : 'bg-emerald-50 text-emerald-500' }} rounded-2xl p-5 shadow-inner border border-gray-50">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            {!! $metric['icon_path'] !!}
                        </svg>
                    </div>
                    <div class="ml-8 w-0 flex-1">
                        <dl>
                            <dt class="text-[11px] font-black text-gray-400 uppercase tracking-widest truncate">{{ $metric['label'] }}</dt>
                            <dd class="flex items-baseline mt-2">
                                <div class="text-5xl font-black {{ $metric['color'] === 'yellow' ? 'text-amber-600' : 'text-emerald-600' }} tracking-tighter">
                                    {{ number_format($metric['value'], 0, ',', '.') }}
                                </div>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 3. TABEL 5 ANTREAN TERAKHIR --}}
    <div class="mt-8">
        <h2 class="text-lg font-black text-gray-900 uppercase tracking-tight mb-6 flex items-center">
            <span class="w-2 h-6 bg-cyan-600 rounded-full mr-3"></span>
            5 Pengecekan Terakhir & Menunggu
        </h2>
        
        <div class="bg-white shadow-sm overflow-hidden rounded-[2rem] border border-gray-100">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th scope="col" class="px-8 py-6 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Kode Pengembalian</th>
                            <th scope="col" class="px-8 py-6 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Waktu Kembali</th>
                            <th scope="col" class="px-8 py-6 text-left text-[11px] font-black text-gray-400 uppercase tracking-widest">Penyewa & Armada</th>
                            <th scope="col" class="px-8 py-6 text-center text-[11px] font-black text-gray-400 uppercase tracking-widest">Status Fisik</th>
                            <th scope="col" class="px-8 py-6 text-right text-[11px] font-black text-gray-400 uppercase tracking-widest">Aksi Lanjutan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @forelse ($latestChecks as $check)
                            @php
                                $statusClass = match(strtolower($check->status)) {
                                    'selesai' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                    'selesai pengecekan' => 'bg-blue-50 text-blue-600 border-blue-200', 
                                    'menunggu pengecekan' => 'bg-amber-50 text-amber-600 border-amber-200',
                                    default => 'bg-gray-50 text-gray-600 border-gray-200'
                                };
                                $statusLabel = match(strtolower($check->status)) {
                                    'selesai' => 'Selesai',
                                    'selesai pengecekan' => 'Selesai Cek',
                                    'menunggu pengecekan' => 'Perlu Dicek',
                                    default => ucfirst($check->status)
                                };
                            @endphp
                            <tr class="hover:bg-cyan-50/20 transition duration-200 group">
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="text-sm font-black text-gray-900 tracking-tight">{{ $check->kode_pengembalian }}</div>
                                    <div class="text-[9px] font-bold text-gray-400 mt-1 uppercase">ID DB: #{{ $check->id }}</div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::parse($check->tanggal_pengembalian)->format('d M Y') }}</div>
                                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">{{ \Carbon\Carbon::parse($check->tanggal_pengembalian)->format('H:i') }} WIB</div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap">
                                    <div class="font-bold text-gray-900 text-xs uppercase">{{ $check->peminjaman->user->name ?? 'Pengguna Tidak Dikenal' }}</div>
                                    <div class="text-[10px] font-black text-cyan-600 uppercase tracking-widest mt-1">
                                        {{ $check->peminjaman->mobil->merek ?? '-' }} ({{ $check->peminjaman->mobil->plat_nomor ?? 'N/A' }})
                                    </div>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-center">
                                    <span class="px-4 py-1.5 inline-flex text-[9px] font-black uppercase tracking-widest rounded-xl border {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 whitespace-nowrap text-right text-sm font-medium">
                                    @if (strtolower($check->status) === 'menunggu pengecekan')
                                        {{-- Link ini akan melempar staff ke Menu Inspeksi Livewire yang sudah ada --}}
                                        <a href="{{ route('inspeksi') }}" wire:navigate class="inline-flex items-center px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-amber-200 transform group-hover:scale-105">
                                            Proses Pengecekan
                                        </a>
                                    @else
                                        {{-- Link ini akan melempar staff ke Menu Pengembalian untuk melihat rekap detailnya --}}
                                        <a href="{{ route('pengembalian') }}" wire:navigate class="text-cyan-600 hover:text-cyan-800 font-black text-[10px] uppercase tracking-widest flex items-center justify-end gap-1">
                                            Lihat Detail
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-16 text-center">
                                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 text-gray-300 mb-4">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">Antrean pengecekan kosong.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- 4. AKSES CEPAT PINTASAN --}}
    <div class="mt-8 pt-8">
        <h2 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-4">Akses Cepat Operasional</h2>
        <div class="flex gap-4">
            <a href="{{ route('inspeksi') }}" wire:navigate
               class="inline-flex items-center px-6 py-4 rounded-2xl shadow-lg shadow-cyan-200/50
                      text-white bg-cyan-600 hover:bg-cyan-700 font-black text-[11px] uppercase tracking-widest
                      transition-all duration-200 transform hover:-translate-y-1">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                Menu Inspeksi Fisik
            </a>
            
            <a href="{{ route('damage-report') }}" wire:navigate
               class="inline-flex items-center px-6 py-4 rounded-2xl shadow-sm border border-gray-200
                      text-gray-600 bg-white hover:bg-gray-50 font-black text-[11px] uppercase tracking-widest
                      transition-all duration-200 transform hover:-translate-y-1">
                <svg class="h-5 w-5 mr-3 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Laporan Kerusakan
            </a>
        </div>
    </div>
</div>