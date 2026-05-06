<div class="p-6 space-y-6">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        .font-inter {
            font-family: 'Inter', sans-serif;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    {{-- 1. BAGIAN HEADER (Universal) --}}
    <div
        class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                @can('read-roles')
                    Dashboard Overview
                @else
                    Dashboard Operasional
                @endcan
            </h1>
            <p class="text-gray-500 mt-1 text-sm">Selamat bekerja kembali, <span
                    class="font-medium text-gray-800">{{ Auth::user()->name }}</span>! Pantau sistem Anda di sini.</p>
        </div>
        <div class="hidden md:flex flex-col items-end mt-4 md:mt-0">
            <span
                class="bg-gray-50 text-gray-600 text-sm font-medium px-4 py-2 rounded-lg border border-gray-200 shadow-sm">
                📅 {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </span>
            @canany(['read-transaksi_pembayaran', 'read-peminjaman'])
                <button wire:click="openExportModal"
                    class="mt-3 inline-flex items-center justify-center bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg shadow-sm transition-colors duration-200 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-1">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Export Data Laporan
                </button>
            @endcanany
        </div>
    </div>

    {{-- 2. SEKSI STATISTIK (Dinamis berdasarkan hak akses) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @canany(['read-transaksi_pembayaran', 'read-peminjaman'])
            {{-- Card Total Armada --}}
            <a href="{{ route('mobil') }}" wire:navigate
                class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-cyan-500 hover:shadow-md transition cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Armada</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $totalMobil ?? 0 }}</p>
                    </div>
                    <div class="bg-cyan-50 p-2.5 rounded-lg text-cyan-600 text-sm font-semibold">
                        Unit
                    </div>
                </div>
            </a>

            {{-- Card Peminjaman Berlangsung --}}
            <a href="{{ route('peminjaman') }}" wire:navigate
                class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-orange-500 hover:shadow-md transition cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-orange-50 p-3 rounded-xl text-orange-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Sedang Disewa</p>
                        <p class="text-xl font-bold text-gray-900 mt-0.5">{{ $mobilDisewa ?? 0 }} Unit</p>
                    </div>
                </div>
            </a>

            {{-- Card Pendapatan --}}
            <a href="{{ route('pembayaran') }}" wire:navigate
                class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-emerald-500 hover:shadow-md transition cursor-pointer">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-emerald-50 p-3 rounded-xl text-emerald-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Omzet</p>
                        <p class="text-xl font-bold text-gray-900 mt-0.5">Rp
                            {{ number_format($totalPendapatan ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </a>

            {{-- Card Verifikasi --}}
            <a href="{{ route('verifikasi') }}" wire:navigate
                class="block bg-white rounded-2xl shadow-sm border border-gray-100 p-5 border-l-4 border-l-rose-500 hover:shadow-md transition cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Butuh Verifikasi</p>
                        <p class="text-2xl font-bold text-rose-600 mt-1">{{ $pendingVerifikasi ?? 0 }}</p>
                    </div>
                    <div class="bg-rose-50 p-2.5 rounded-lg text-rose-600 text-sm font-semibold">
                        User
                    </div>
                </div>
            </a>
        @endcanany

        {{-- Metrik Khusus Staff / Inspeksi Fisik --}}
        @if (!auth()->user()->can('read-roles') && auth()->user()->can('read-inspeksi_mobil'))
            @if (isset($metrics) && is_array($metrics))
                @foreach ($metrics as $metric)
                    <a href="{{ route('inspeksi') }}" wire:navigate
                        class="block bg-white shadow-sm rounded-2xl p-5 border-l-4 border-l-{{ $metric['color'] ?? 'green' }}-500 hover:shadow-md transition cursor-pointer">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 bg-{{ $metric['color'] ?? 'green' }}-50 p-3 rounded-xl text-{{ $metric['color'] ?? 'green' }}-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    {!! $metric['icon_path'] ??
                                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>' !!}
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ $metric['label'] ?? 'Metrik' }}</p>
                                <p class="text-xl font-bold text-gray-900 mt-0.5">
                                    {{ number_format($metric['value'] ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            @endif
        @endif
    </div>

    {{-- 3. AREA VISUALISASI (Grafik - Hanya Admin & Resepsionis / Finance) --}}
    @canany(['read-transaksi_pembayaran', 'read-peminjaman'])
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-base font-semibold text-gray-800">Tren Pendapatan Tahun Ini</h3>
                    <div class="inline-flex bg-gray-100/80 p-1 rounded-xl">
                        <button data-filter="combined" class="chart-filter-btn px-4 py-1.5 text-xs font-bold rounded-lg bg-white shadow-sm text-cyan-700 transition-all">Keseluruhan</button>
                        <button data-filter="revenue" class="chart-filter-btn px-4 py-1.5 text-xs font-bold rounded-lg text-gray-500 hover:text-gray-700 transition-all">Hanya Sewa</button>
                        <button data-filter="fine" class="chart-filter-btn px-4 py-1.5 text-xs font-bold rounded-lg text-gray-500 hover:text-gray-700 transition-all">Hanya Denda</button>
                    </div>
                </div>
                <div id="revenueChart" class="min-h-[300px] w-full flex-grow"></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-base font-semibold text-gray-800 mb-6 text-center">Top 5 Armada Paling Laris</h3>
                <div id="topCarChart" class="flex justify-center items-center h-full min-h-[300px]"></div>
            </div>
        </div>
    @endcanany

    {{-- 4. AREA OPERASIONAL --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            {{-- SEKSI TUGAS SOPIR --}}
            @canany(['read-roles', 'read-logbook_sopir'])
                @if (isset($tugasAktif))
                    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-5 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800">Jadwal Operasional Driver</h3>
                            @if (isset($sopir) && $sopir)
                                <span
                                    class="px-2.5 py-1 bg-cyan-100 text-cyan-700 text-xs font-medium rounded-md capitalize">
                                    Status: {{ str_replace('_', ' ', $sopir->status) }}
                                </span>
                            @endif
                        </div>
                        <div class="p-6">
                            @if (isset($sopir) && $sopir && $sopir->status === 'tidak tersedia')
                                <div class="py-10 text-center">
                                    <p class="text-base font-medium text-gray-600">Status Anda Saat Ini Tidak Tersedia</p>
                                    <p class="text-sm text-gray-400 mt-2">Gunakan toggle di sidebar untuk mengaktifkan
                                        status kerja Anda.</p>
                                </div>
                            @else
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @forelse($tugasAktif as $t)
                                        <div
                                            class="bg-white border border-gray-200 p-5 rounded-xl hover:border-cyan-400 hover:shadow-sm transition group">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="text-base font-semibold text-gray-800">{{ $t->mobil->merek }}
                                                    </p>
                                                    <p class="text-xs text-gray-500 mt-1">{{ $t->user->name }} &bull;
                                                        {{ $t->mobil_id }}</p>
                                                </div>
                                                <span
                                                    class="px-2.5 py-1 bg-blue-50 text-blue-600 text-xs font-medium rounded-md capitalize">{{ $t->status }}</span>
                                            </div>
                                            <div
                                                class="mt-5 flex justify-between items-center border-t border-gray-100 pt-4">
                                                <p class="text-xs text-gray-500">Sewa:
                                                    {{ date('d M', strtotime($t->tanggal_sewa)) }} -
                                                    {{ date('d M', strtotime($t->tanggal_kembali)) }}</p>
                                                <button
                                                    class="text-xs font-medium text-cyan-600 group-hover:text-cyan-700">Detail
                                                    Tugas &rarr;</button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-span-2 py-10 text-center">
                                            <p class="text-gray-500 text-sm">Belum ada penugasan aktif hari ini.</p>
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </section>
                @endif
            @endcanany

            {{-- SEKSI ANTRIAN INSPEKSI --}}
            @canany(['read-roles', 'read-inspeksi_mobil'])
                @if (isset($latestChecks))
                    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-5 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800">Antrian Pengecekan Kendaraan</h3>
                            <a href="{{ route('inspeksi') }}"
                                class="text-sm font-medium text-orange-600 hover:text-orange-700">Cari Pengembalian
                                &rarr;</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-white text-gray-500 font-medium border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 font-medium">Kode / Mobil</th>
                                        <th class="px-6 py-3 font-medium text-center">Status</th>
                                        <th class="px-6 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($latestChecks as $c)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4">
                                                <p class="text-sm font-semibold text-gray-800">{{ $c->kode_pengembalian }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    {{ $c->peminjaman->mobil->merek ?? '-' }} &bull;
                                                    {{ $c->peminjaman->user->name ?? '-' }}</p>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span
                                                    class="px-3 py-1 rounded-md text-xs font-medium {{ $c->status == 'menunggu pengecekan' ? 'bg-orange-50 text-orange-600' : 'bg-emerald-50 text-emerald-600' }}">
                                                    {{ $c->status == 'menunggu pengecekan' ? 'Perlu Cek' : 'Selesai' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                @if ($c->status == 'menunggu pengecekan')
                                                    <a href="{{ route('inspeksi') }}"
                                                        class="text-xs font-medium bg-white border border-gray-200 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition">Proses</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-10 text-center text-gray-500 text-sm">Antrian
                                                inspeksi unit bersih.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            @endcanany

            {{-- RIWAYAT TRANSAKSI --}}
            @canany(['read-roles', 'read-transaksi_pembayaran', 'read-peminjaman'])
                @if (isset($recentTransactions))
                    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-5 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-800">Riwayat Transaksi Masuk</h3>
                            <a href="{{ route('peminjaman') }}"
                                class="text-sm font-medium text-cyan-600 hover:text-cyan-700">Lihat Semua &rarr;</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-white text-gray-500 font-medium border-b border-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 font-medium">Pelanggan</th>
                                        <th class="px-6 py-3 font-medium">Armada</th>
                                        <th class="px-6 py-3 font-medium text-right">Nilai Sewa</th>
                                        <th class="px-6 py-3 font-medium text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($recentTransactions as $rt)
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4">
                                                <p class="text-sm font-medium text-gray-800">{{ $rt->user->name ?? '-' }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $rt->user->email ?? '-' }}</p>
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="text-sm text-gray-700">{{ $rt->mobil->merek ?? '-' }} <span
                                                        class="bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded text-xs ml-1">{{ $rt->mobil_id }}</span>
                                                </p>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <p class="text-sm font-semibold text-gray-800">Rp
                                                    {{ number_format($rt->total_harga ?? 0, 0, ',', '.') }}</p>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="text-xs font-medium text-gray-600 capitalize">
                                                    {{ $rt->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            @endcanany
        </div>

        {{-- KOLOM KANAN --}}
        <div class="space-y-6">
            @canany(['read-roles', 'read-pembatalan_pesanan', 'read-pengembalian'])
                <a href="{{ route('pembatalan') }}" wire:navigate
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-rose-200 transition cursor-pointer">
                    <div class="flex items-center gap-4">
                        <div class="bg-rose-50 text-rose-500 w-12 h-12 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Batal Pesan</h3>
                            <p class="text-xs text-gray-500 mt-1">Total: <span
                                    class="font-semibold text-gray-700">{{ $totalPembatalan ?? 0 }}</span></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span
                            class="block text-2xl font-bold text-gray-900 leading-none">{{ $pendingPembatalan ?? 0 }}</span>
                        <span class="text-xs text-rose-500 font-medium mt-1 block">Pending</span>
                    </div>
                </a>

                <a href="{{ route('pengembalian') }}" wire:navigate
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-emerald-200 transition cursor-pointer">
                    <div class="flex items-center gap-4">
                        <div class="bg-emerald-50 text-emerald-500 w-12 h-12 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Order Sukses</h3>
                            <p class="text-xs text-gray-500 mt-1">Unit Kembali</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span
                            class="block text-2xl font-bold text-gray-900 leading-none">{{ $peminjamanSelesai ?? 0 }}</span>
                        <span class="text-xs text-emerald-500 font-medium mt-1 block">Selesai</span>
                    </div>
                </a>
            @endcanany

            @can('read-users')
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-800 mb-5">Statistik Sistem</h3>
                    <div class="space-y-4">
                        <a href="{{ route('users') }}" wire:navigate
                            class="flex items-center justify-between hover:opacity-75 transition cursor-pointer">
                            <span class="text-sm text-gray-600">Total Pelanggan</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $totalPelanggan ?? 0 }}</span>
                        </a>
                        <a href="{{ route('users') }}" wire:navigate
                            class="flex items-center justify-between border-t border-gray-100 pt-4 hover:opacity-75 transition cursor-pointer">
                            <span class="text-sm text-gray-600">Tim Operasional</span>
                            <span class="text-sm font-semibold text-gray-900">{{ $operationalCount ?? 0 }}</span>
                        </a>
                    </div>
                </div>
            @endcan

            <div
                class="bg-gradient-to-br from-cyan-700 to-cyan-900 p-6 rounded-2xl shadow-sm text-white relative overflow-hidden group">
                <div class="relative z-10">
                    <h4 class="text-sm font-semibold mb-2">Pusat Bantuan</h4>
                    <p class="text-xs text-cyan-100 leading-relaxed mb-4">Butuh bantuan teknis terkait operasional
                        sistem persewaan?</p>
                    <a href="#"
                        class="inline-flex items-center justify-center bg-white text-cyan-800 hover:bg-cyan-50 px-4 py-2 rounded-lg text-xs font-medium transition-colors">
                        Hubungi IT
                        <svg class="w-3 h-3 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. MODAL EXPORT LAPORAN --}}
    @if ($showExportModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm">
            <div class="relative p-4 w-full max-w-md max-h-full">
                <div class="relative bg-white rounded-2xl shadow-lg border border-gray-100">
                    <div class="flex items-center justify-between p-4 md:p-5 border-b border-gray-100 rounded-t">
                        <h3 class="text-lg font-bold text-gray-900">
                            Export Laporan PDF
                        </h3>
                        <button wire:click="$set('showExportModal', false)" type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-100 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>

                    <div class="p-4 md:p-5">
                        <form wire:submit.prevent="downloadReport" class="space-y-5">

                            {{-- Jenis Laporan --}}
                            <div>
                                <label class="block mb-2 text-sm font-semibold text-gray-800">Jenis Laporan</label>
                                <select wire:model.live="exportType"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-cyan-500 focus:border-cyan-500 block w-full p-2.5">
                                    <option value="peminjaman">Data Operasional (Peminjaman)</option>
                                    <option value="pembayaran">Data Keuangan (Pembayaran)</option>
                                    <option value="denda">Data Penerimaan (Denda)</option>
                                </select>
                                @error('exportType')
                                    <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Rentang Tanggal --}}
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-2 text-sm font-semibold text-gray-800">Dari Tanggal</label>
                                    <input type="date" wire:model="exportStartDate"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-cyan-500 focus:border-cyan-500 block w-full p-2.5"
                                        required>
                                    @error('exportStartDate')
                                        <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-semibold text-gray-800">Sampai
                                        Tanggal</label>
                                    <input type="date" wire:model="exportEndDate"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-cyan-500 focus:border-cyan-500 block w-full p-2.5"
                                        required>
                                    @error('exportEndDate')
                                        <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            {{-- Filter Status Dinamis --}}
                            <div>
                                <label class="block mb-2 text-sm font-semibold text-gray-800">Filter Status</label>
                                <select wire:model="exportStatus"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-cyan-500 focus:border-cyan-500 block w-full p-2.5">
                                    <option value="all">-- Semua Status --</option>

                                    @if ($exportType === 'peminjaman')
                                        <option value="menunggu pembayaran">Menunggu Pembayaran</option>
                                        <option value="pembayaran dp">Sudah DP</option>
                                        <option value="berlangsung">Sedang Berlangsung</option>
                                        <option value="selesai">Selesai Dikembalikan</option>
                                        <option value="dibatalkan">Dibatalkan</option>
                                    @elseif($exportType === 'pembayaran')
                                        <option value="success">Success / Berhasil</option>
                                        <option value="settlement">Settlement / Lunas</option>
                                        <option value="pending">Pending</option>
                                        <option value="expire">Expire / Gagal / Kadaluarsa</option>
                                    @elseif($exportType === 'denda')
                                        <option value="belum dibayar">Belum Dibayar</option>
                                        <option value="sudah dibayar">Sudah Dibayar</option>
                                    @endif

                                </select>
                                @error('exportStatus')
                                    <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Action Button --}}
                            <div class="pt-2">
                                <button type="submit"
                                    class="w-full text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:outline-none focus:ring-cyan-200 font-medium rounded-xl text-sm px-5 py-3 text-center transition-all flex justify-center items-center disabled:opacity-50">
                                    <span wire:loading.remove wire:target="downloadReport" class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                            </path>
                                        </svg>
                                        Unduh PDF Sekarang
                                    </span>
                                    <span wire:loading wire:target="downloadReport" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- APEXCHARTS --}}
    @canany(['read-transaksi_pembayaran', 'read-peminjaman'])
        @if (isset($revenueData) && isset($topMobilData))
            <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
            <script>
                document.addEventListener('livewire:navigated', () => {
                    // Simpan data dari backend
                    const rawRevenueData = @json($revenueData ?? []);
                    const rawFineData = @json($fineData ?? []);
                    const rawCombinedData = @json($combinedData ?? []);

                    // Fungsi format untuk Data Label di Grafik agar ringkas (1.5Jt / 500Rb)
                    const formatCompactCurrency = (val) => {
                        if (val === 0) return '';
                        if (val >= 1000000) return (val / 1000000).toFixed(1).replace('.0', '') + 'Jt';
                        if (val >= 1000) return (val / 1000).toFixed(0) + 'Rb';
                        return val;
                    };

                    const chartConfig = {
                        chart: {
                            toolbar: { show: false },
                            zoom: { enabled: false },
                            fontFamily: 'Inter, sans-serif'
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        dataLabels: {
                            enabled: true, // Label selalu menyala tanpa kursor
                            formatter: function (val) {
                                return formatCompactCurrency(val);
                            },
                            offsetY: -5,
                            style: {
                                fontSize: '10px',
                                colors: ['#0891b2']
                            },
                            background: {
                                enabled: true,
                                foreColor: '#fff',
                                borderRadius: 4,
                                padding: 4,
                                opacity: 0.9,
                                borderWidth: 1,
                                borderColor: '#0891b2'
                            }
                        },
                        grid: {
                            borderColor: '#f1f5f9',
                            strokeDashArray: 4,
                            padding: { top: 20 }
                        },
                        colors: ['#0891b2'],
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.4,
                                opacityTo: 0.05
                            }
                        },
                        xaxis: {
                            categories: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                            axisBorder: { show: false },
                            labels: { style: { colors: '#64748b' } }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#64748b' },
                                formatter: function (val) {
                                    return formatCompactCurrency(val);
                                }
                            }
                        },
                        tooltip: {
                            y: {
                                // Detail lengkap menggunakan format Rupiah panjang ketika di hover
                                formatter: val => "Rp " + new Intl.NumberFormat('id-ID').format(val)
                            }
                        }
                    };

                    const revenueChart = new ApexCharts(document.querySelector("#revenueChart"), {
                        ...chartConfig,
                        series: [{
                            name: 'Total Gabungan',
                            data: rawCombinedData
                        }],
                        chart: {
                            ...chartConfig.chart,
                            type: 'area',
                            height: 300
                        }
                    });
                    
                    revenueChart.render();

                    // Logic Filter Segmented Control Tanpa Refresh Page
                    const filterBtns = document.querySelectorAll('.chart-filter-btn');
                    filterBtns.forEach(btn => {
                        btn.addEventListener('click', function() {
                            // Update active state class UI
                            filterBtns.forEach(b => {
                                b.classList.remove('bg-white', 'shadow-sm', 'text-cyan-700');
                                b.classList.add('text-gray-500');
                            });
                            this.classList.remove('text-gray-500');
                            this.classList.add('bg-white', 'shadow-sm', 'text-cyan-700');

                            let selectedVal = this.getAttribute('data-filter');
                            let targetData = rawCombinedData;
                            let seriesName = 'Total Gabungan';
                            let chartColor = '#0891b2'; // Cyan default

                            if(selectedVal === 'revenue') {
                                targetData = rawRevenueData;
                                seriesName = 'Penyewaan Saja';
                                chartColor = '#0ea5e9'; // Biru cerah
                            } else if (selectedVal === 'fine') {
                                targetData = rawFineData;
                                seriesName = 'Denda Saja';
                                chartColor = '#f59e0b'; // Oranye
                            }

                            // Update Data Chart
                            revenueChart.updateSeries([{
                                name: seriesName,
                                data: targetData
                            }]);
                            
                            // Update Warna Grafik agar lebih dinamis
                            revenueChart.updateOptions({
                                colors: [chartColor],
                                dataLabels: {
                                    style: { colors: [chartColor] },
                                    background: { borderColor: chartColor }
                                }
                            });
                        });
                    });

                    // Chart Pie Top Mobil
                    new ApexCharts(document.querySelector("#topCarChart"), {
                        series: @json($topMobilData),
                        labels: @json($topMobilLabels),
                        chart: {
                            type: 'donut',
                            height: 320,
                            fontFamily: 'Inter, sans-serif'
                        },
                        colors: ['#0891b2', '#0ea5e9', '#38bdf8', '#7dd3fc', '#bae6fd'],
                        legend: {
                            position: 'bottom',
                            fontSize: '13px',
                            fontWeight: 500,
                            labels: {
                                colors: '#475569'
                            }
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '70%',
                                    labels: {
                                        show: true,
                                        name: { fontWeight: 600, color: '#475569' },
                                        value: { fontWeight: 600, color: '#0f172a' }
                                    }
                                }
                            }
                        }
                    }).render();
                });
            </script>
        @endif
    @endcanany
</div>