<div class="p-6 space-y-8">
{{-- 1. BAGIAN HEADER (Universal) --}}
<div class="flex flex-col md:flex-row justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
<div>
<h1 class="text-3xl font-extrabold text-gray-900 tracking-tight uppercase italic">
@role('admin') Dashboard Overview @else Dashboard {{ ucfirst(auth()->user()->getRoleNames()->first()) }} @endrole
</h1>
<p class="text-gray-500 mt-1 font-medium italic">Selamat bekerja kembali, {{ Auth::user()->name }}! Pantau sistem Anda di sini.</p>
</div>
<div class="hidden md:flex flex-col items-end">
<span class="bg-cyan-50 text-cyan-700 text-xs font-black px-4 py-2 rounded-xl border border-cyan-100 shadow-sm uppercase tracking-widest">
📅 {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
</span>
@role('admin|resepsionis')
<button wire:click="$set('showExportModal', true)" class="mt-2 text-[10px] font-black text-cyan-600 hover:text-cyan-800 underline uppercase tracking-tighter">Export Data Laporan</button>
@endrole
</div>
</div>

{{-- 2. SEKSI STATISTIK (Dinamis berdasarkan hak akses) --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    @role('admin|resepsionis')
        {{-- Card Total Armada --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-cyan-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Armada</p>
                    <p class="text-2xl font-black text-gray-800 mt-1">{{ $totalMobil }}</p>
                </div>
                <div class="bg-cyan-50 p-3 rounded-xl text-cyan-600 font-bold italic">UNIT</div>
            </div>
        </div>

        {{-- Card Peminjaman Berlangsung --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-orange-500">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-orange-100 p-3 rounded-full text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Sedang Disewa</p>
                    <p class="text-xl font-black text-gray-800">{{ $mobilDisewa }} Unit</p>
                </div>
            </div>
        </div>

        {{-- Card Pendapatan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-emerald-500">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-emerald-100 p-3 rounded-full text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Total Omzet</p>
                    <p class="text-xl font-black text-gray-800">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Card Verifikasi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-rose-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest">Butuh Verifikasi</p>
                    <p class="text-2xl font-black text-rose-600 mt-1">{{ $pendingVerifikasi }}</p>
                </div>
                <div class="bg-rose-50 p-3 rounded-xl text-rose-600 font-bold italic">USER</div>
            </div>
        </div>
    @endrole

    {{-- Metrik Khusus Staff (Hanya muncul jika bukan Admin tapi Staff) --}}
    @if(!auth()->user()->hasRole('admin') && auth()->user()->hasRole('staff'))
        @foreach ($metrics as $metric)
            <div class="bg-white shadow-sm rounded-2xl p-6 border-b-4 border-{{ $metric['color'] === 'yellow' ? 'yellow' : 'green' }}-500">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-{{ $metric['color'] === 'yellow' ? 'yellow' : 'green' }}-600 rounded-xl p-3 text-white shadow-md">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $metric['icon_path'] !!}</svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-widest">{{ $metric['label'] }}</p>
                        <p class="text-2xl font-black text-gray-900">{{ number_format($metric['value'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

{{-- 3. AREA VISUALISASI (Grafik - Hanya Admin & Resepsionis) --}}
@role('admin|resepsionis')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-sm font-black text-gray-700 uppercase tracking-widest mb-6 italic">Tren Pendapatan Tahun Ini</h3>
        <div id="revenueChart" class="min-h-[300px]"></div>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="text-sm font-black text-gray-700 uppercase tracking-widest mb-6 italic text-center">Top 5 Armada Paling Laris</h3>
        <div id="topCarChart" class="flex justify-center items-center h-full"></div>
    </div>
</div>
@endrole

{{-- 4. AREA OPERASIONAL --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 space-y-8">
        {{-- SEKSI TUGAS SOPIR --}}
        @role('admin|sopir')
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-cyan-50 border-b border-cyan-100 flex items-center justify-between">
                <h3 class="text-xs font-black text-cyan-800 uppercase tracking-widest">Jadwal Operasional Driver</h3>
                @if($sopir) <span class="px-2 py-0.5 bg-cyan-600 text-white text-[10px] font-bold rounded-lg uppercase tracking-tighter shadow-sm">Status: {{ $sopir->status }}</span> @endif
            </div>
            <div class="p-6">
                @if($sopir && $sopir->status === 'tidak tersedia')
                    <div class="py-10 text-center">
                        <p class="text-sm text-rose-500 font-bold uppercase italic">Status Anda Saat Ini Tidak Tersedia</p>
                        <p class="text-xs text-gray-400 mt-1">Gunakan toggle di sidebar untuk mengaktifkan status kerja Anda.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @forelse($tugasAktif as $t)
                            <div class="bg-gray-50 border border-gray-100 p-4 rounded-2xl hover:border-cyan-300 transition group">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-black text-gray-800 uppercase italic">{{ $t->mobil->merek }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ $t->user->name }} — {{ $t->mobil_id }}</p>
                                    </div>
                                    <span class="px-2 py-1 bg-blue-100 text-blue-600 text-[10px] font-black rounded uppercase">{{ $t->status }}</span>
                                </div>
                                <div class="mt-4 flex justify-between items-center">
                                    <p class="text-[10px] text-gray-400 font-medium">Sewa: {{ date('d M', strtotime($t->tanggal_sewa)) }} - {{ date('d M', strtotime($t->tanggal_kembali)) }}</p>
                                    <button class="text-[10px] font-black text-cyan-600 group-hover:underline uppercase tracking-widest">Detail Tugas &rarr;</button>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-2 py-10 text-center text-gray-400 italic text-xs font-medium">Belum ada penugasan aktif hari ini.</div>
                        @endforelse
                    </div>
                @endif
            </div>
        </section>
        @endrole

        {{-- SEKSI ANTRIAN INSPEKSI --}}
        @role('admin|staff')
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-orange-50 border-b border-orange-100 flex items-center justify-between">
                <h3 class="text-xs font-black text-orange-800 uppercase tracking-widest">Antrian Pengecekan Kendaraan</h3>
                <a href="{{ route('inspeksi') }}" class="text-[10px] font-black text-orange-600 hover:underline uppercase tracking-tighter italic">Cari Pengembalian &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                        <tr><th class="px-6 py-4">Kode / Mobil</th><th class="px-6 py-4 text-center">Status</th><th class="px-6 py-4"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($latestChecks as $c)
                        <tr class="hover:bg-orange-50/30 transition">
                            <td class="px-6 py-4 italic">
                                <p class="text-sm font-black text-gray-800">{{ $c->kode_pengembalian }}</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ $c->peminjaman->mobil->merek }} — {{ $c->peminjaman->user->name }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $c->status == 'menunggu pengecekan' ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600' }}">
                                    {{ $c->status == 'menunggu pengecekan' ? 'Perlu Cek' : 'Selesai' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($c->status == 'menunggu pengecekan')
                                    <a href="{{ route('inspeksi') }}" class="text-[10px] font-black bg-orange-600 text-white px-3 py-1.5 rounded-xl transition hover:bg-orange-700 uppercase tracking-widest shadow-sm">PROSES</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-10 text-center text-gray-400 italic text-xs font-medium">Antrian inspeksi unit bersih.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @endrole

        {{-- RIWAYAT TRANSAKSI --}}
        @role('admin|resepsionis')
        <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-emerald-50 border-b border-emerald-100 flex items-center justify-between">
                <h3 class="text-xs font-black text-emerald-800 uppercase tracking-widest">Riwayat Transaksi Masuk</h3>
                <a href="{{ route('peminjaman') }}" class="text-[10px] font-black text-emerald-600 hover:underline uppercase tracking-tighter italic">Lihat Semua &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-white text-[10px] font-black text-gray-400 uppercase tracking-widest border-b">
                        <tr><th class="px-6 py-4">Pelanggan</th><th class="px-6 py-4">Armada</th><th class="px-6 py-4 text-right text-emerald-600">Nilai Sewa</th><th class="px-6 py-4 text-center">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentTransactions as $rt)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4">
                                <p class="text-sm font-black text-gray-800">{{ $rt->user->name }}</p>
                                <p class="text-[10px] text-gray-400 font-bold tracking-tighter">{{ $rt->user->email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-xs font-bold text-gray-600 italic">{{ $rt->mobil->merek }} <span class="bg-gray-100 px-1 rounded not-italic tracking-tighter text-[10px]">{{ $rt->mobil_id }}</span></p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <p class="text-sm font-black text-emerald-600 italic font-mono">Rp {{ number_format($rt->total_harga) }}</p>
                            </td>
                            <td class="px-6 py-4 text-center uppercase tracking-tighter font-black text-[10px] text-gray-600">
                                {{ $rt->status }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endrole
    </div>

    {{-- KOLOM KANAN --}}
    <div class="space-y-6">
        @role('admin|resepsionis')
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-rose-200 transition">
            <div class="flex items-center gap-4">
                <div class="bg-rose-50 text-rose-600 p-4 rounded-2xl italic font-black">!</div>
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Batal Pesan</h3>
                    <p class="text-xs text-gray-400 font-medium italic mt-1">Total: <span class="font-black text-gray-800">{{ $totalPembatalan }}</span></p>
                </div>
            </div>
            <div class="text-right">
                <span class="block text-3xl font-black text-rose-600 leading-none">{{ $pendingPembatalan }}</span>
                <span class="text-[10px] text-rose-500 font-black uppercase tracking-tighter italic">Pending</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-emerald-200 transition">
            <div class="flex items-center gap-4">
                <div class="bg-emerald-50 text-emerald-600 p-4 rounded-2xl">✓</div>
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Order Sukses</h3>
                    <p class="text-xs text-gray-400 font-medium italic mt-1">Unit Kembali</p>
                </div>
            </div>
            <div class="text-right">
                <span class="block text-3xl font-black text-emerald-600 leading-none">{{ $peminjamanSelesai }}</span>
                <span class="text-[10px] text-emerald-500 font-black uppercase tracking-tighter italic">Selesai</span>
            </div>
        </div>
        @endrole

        @role('admin')
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 italic">Statistik Sistem</h3>
            <div class="space-y-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-gray-500">Total Pelanggan</span>
                    </div>
                    <span class="text-sm font-black text-gray-800">{{ $totalPelanggan }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-bold text-gray-500">Tim Operasional</span>
                    </div>
                    <span class="text-sm font-black text-gray-800 italic">{{ $operationalCount }}</span>
                </div>
            </div>
        </div>
        @endrole

        <div class="bg-gradient-to-br from-cyan-600 to-cyan-800 p-6 rounded-2xl shadow-lg text-white relative overflow-hidden group">
            <div class="relative z-10">
                <h4 class="text-xs font-black uppercase tracking-widest opacity-80 mb-1">Help Desk</h4>
                <p class="text-[10px] leading-relaxed opacity-90 italic">Butuh bantuan teknis terkait operasional sistem persewaan?</p>
                <a href="#" class="mt-4 inline-block bg-white/20 hover:bg-white/30 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all transform group-hover:translate-x-1">Hubungi IT &rarr;</a>
            </div>
        </div>
    </div>
</div>

{{-- APEXCHARTS --}}
@role('admin|resepsionis')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('livewire:navigated', () => {
        const chartConfig = {
            chart: { toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'Inter, sans-serif' },
            stroke: { curve: 'smooth', width: 4 },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f1f1', strokeDashArray: 4 },
        };

        new ApexCharts(document.querySelector("#revenueChart"), {
            ...chartConfig,
            series: [{ name: 'Pendapatan', data: @json($chartData) }],
            chart: { ...chartConfig.chart, type: 'area', height: 300 },
            colors: ['#0891b2'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05 } },
            xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'], axisBorder: { show: false } },
            tooltip: { y: { formatter: val => "Rp " + new Intl.NumberFormat('id-ID').format(val) } }
        }).render();

        new ApexCharts(document.querySelector("#topCarChart"), {
            series: @json($topMobilData),
            labels: @json($topMobilLabels),
            chart: { type: 'donut', height: 320 },
            colors: ['#0891b2', '#0e7490', '#155e75', '#164e63', '#064e3b'],
            legend: { position: 'bottom', fontSize: '10px', fontWeight: 900 },
            plotOptions: { pie: { donut: { size: '75%', labels: { show: true, name: { fontWeight: 900 }, value: { fontWeight: 900 } } } } }
        }).render();
    });
</script>
@endrole


</div>