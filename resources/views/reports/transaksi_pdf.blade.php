<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'Laporan Transaksi' }}</title>
    <style>
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            /* Mengurangi hierarki font dasar agar kolom panjang bisa muat */
            font-size: 10px; 
            color: #333;
        }
        
        /* Gaya Kop Surat */
        .kop-surat {
            width: 100%;
            border-bottom: 3px solid #000; /* Garis tebal */
            padding-bottom: 10px;
            margin-bottom: 2px;
        }
        .kop-surat-tengah {
            border-bottom: 1px solid #000; /* Garis tipis */
            margin-bottom: 20px;
        }
        .kop-surat table {
            width: 100%;
            border: none;
            margin: 0;
        }
        .kop-surat td {
            border: none;
            padding: 0;
        }
        .logo-container {
            width: 15%;
            text-align: center;
            vertical-align: middle;
        }
        .teks-kop {
            width: 85%;
            text-align: center;
            vertical-align: middle;
            font-family: 'Times New Roman', Times, serif; 
        }
        .teks-kop h1 {
            margin: 0 0 8px 0;
            font-size: 22px;
            font-weight: bold;
            color: #000;
            letter-spacing: 0.5px;
        }
        .teks-kop p {
            margin: 5px 0;
            font-size: 13px;
            color: #000;
            font-weight: bold;
        }

        /* Gaya Konten */
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            margin-top: 10px;
        }
        .header h2 { 
            margin: 0; 
            font-size: 14px; 
            font-weight: bold; 
            text-transform: uppercase;
        }
        .header p { 
            margin: 5px 0 0 0; 
            color: #555; 
        }

        /* Gaya Tabel Data */
        table.data-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        table.data-table th, table.data-table td { 
            border: 1px solid #777; 
            padding: 6px 5px; /* Padding disesuaikan agar lebih ringkas */
            text-align: left; 
        }
        table.data-table th { 
            background-color: #f1f5f9; 
            font-weight: bold;
            text-align: center;
            font-size: 10px;
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        
        /* Gaya Baris Rincian Total */
        .summary-row td {
            padding: 5px 6px;
            border: 1px solid #777;
        }
        .summary-label {
            text-align: right;
            font-weight: bold;
        }
        .summary-value {
            text-align: right;
            font-weight: bold;
        }
        .summary-net td {
            background-color: #f1f5f9;
            font-size: 11px;
        }
        
        .footer { 
            margin-top: 40px; 
            width: 100%;
        }
        .footer-table {
            width: 100%;
            border: none;
        }
        .footer-table td {
            border: none;
        }
        .signature-box {
            width: 30%;
            float: right;
            text-align: center;
        }
        .signature-space {
            height: 70px;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <div class="kop-surat">
        <table>
            <tr>
                <td class="logo-container">
                    <?php
                        $imagePath = public_path('logoakarentcar.png');
                        $logoData = '';
                        if(file_exists($imagePath)){
                            $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($imagePath));
                        }
                    ?>
                    @if($logoData)
                        <img src="{{ $logoData }}" style="max-width: 90px; height: auto;">
                    @else
                        <h2 style="margin:0;">AKA RENT</h2>
                    @endif
                </td>
                <td class="teks-kop">
                    <h1>PT.AKA MENDUNIA SEJAHTERA</h1>
                    <p>Nomor : AHU-054806.AH.01.30.Tahun 2022</p>
                    <p>Jl. Kutu Patran, Sendangadi, Sleman Yogyakarta</p>
                    <p>Email: <span style="color: #0563C1; text-decoration: underline;">pt.aka.mendunia@gmail.com</span>, Web :akarental.site</p>
                </td>
            </tr>
        </table>
    </div>
    <div class="kop-surat-tengah"></div>

    {{-- JUDUL LAPORAN --}}
    <div class="header">
        <h2>{{ $title ?? 'LAPORAN TRANSAKSI RENTAL MOBIL' }}</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->locale('id')->isoFormat('D MMMM Y') }} - {{ \Carbon\Carbon::parse($endDate)->locale('id')->isoFormat('D MMMM Y') }}</p>
    </div>

    {{-- TABEL DATA DINAMIS --}}
    <table class="data-table">
        
        {{-- ================================================================ --}}
        {{-- 1. TABEL PEMINJAMAN --}}
        {{-- ================================================================ --}}
        @if($reportType === 'peminjaman')
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="18%">Tanggal Sewa</th>
                <th width="22%">Nama Penyewa</th>
                <th width="20%">Unit Armada</th>
                <th width="20%">Nama Sopir</th>
                <th width="15%" class="text-right">Nominal Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalGross = 0; 
                $totalSopir = 0; 
                $totalPajak = 0;
                $totalMitra = 0;
                $totalNet = 0; 
            @endphp
            @forelse($data as $index => $row)
                @php
                    $kotor = $row->total_harga ?? 0;
                    
                    // 1. Estimasi biaya sopir
                    $biayaSopir = 0;
                    if($row->sopir_id) {
                        $days = max(1, \Carbon\Carbon::parse($row->tanggal_sewa)->diffInDays(\Carbon\Carbon::parse($row->tanggal_kembali)));
                        $biayaSopir = 1500 * $days;
                    }
                    
                    // 2. Kalkulasi Pajak 11% dari harga sewa murni
                    $sewaMurni = max(0, $kotor - $biayaSopir);
                    $pendapatanBersih = round($sewaMurni / 1.11);
                    $pajak = $sewaMurni - $pendapatanBersih;
                    
                    // 3. Kalkulasi Hak Mitra
                    $hakRental = $pendapatanBersih;
                    $hakMitra = 0;
                    
                    if($row->mobil && $row->mobil->status_kepemilikan === 'mitra') {
                        $persenRental = $row->mobil->persentase_bagi_hasil_rental ?? 100;
                        $hakRental = round($pendapatanBersih * ($persenRental / 100));
                        $hakMitra = $pendapatanBersih - $hakRental;
                    }
                    
                    $totalGross += $kotor;
                    $totalSopir += $biayaSopir;
                    $totalPajak += $pajak;
                    $totalMitra += $hakMitra;
                    $totalNet += $hakRental;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($row->tanggal_sewa)->format('d/m/Y') }}<br>
                        <small>s/d {{ \Carbon\Carbon::parse($row->tanggal_kembali)->format('d/m/Y') }}</small>
                    </td>
                    <td>{{ $row->user->name ?? 'N/A' }}</td>
                    <td>{{ $row->mobil->merek ?? 'N/A' }} <small>({{ $row->mobil->id ?? '-' }})</small></td>
                    <td>{{ $row->sopir ? ($row->sopir->user->name ?? 'Terhapus') : 'Tanpa Sopir' }}</td>
                    <td class="text-right">Rp {{ number_format($kotor, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Tidak ada data yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($data) > 0)
        <tfoot>
            <tr class="summary-row" style="background-color: #f8fafc;">
                <td colspan="5" class="summary-label">TOTAL OMSET KESELURUHAN (KOTOR)</td>
                <td class="summary-value">Rp {{ number_format($totalGross, 0, ',', '.') }}</td>
            </tr>
            <tr class="summary-row">
                <td colspan="5" class="summary-label" style="color: #059669;">POTONGAN PENDAPATAN SOPIR</td>
                <td class="summary-value" style="color: #059669;">- Rp {{ number_format($totalSopir, 0, ',', '.') }}</td>
            </tr>
            <tr class="summary-row">
                <td colspan="5" class="summary-label" style="color: #dc2626;">POTONGAN PAJAK PPN (11%)</td>
                <td class="summary-value" style="color: #dc2626;">- Rp {{ number_format($totalPajak, 0, ',', '.') }}</td>
            </tr>
            <tr class="summary-row">
                <td colspan="5" class="summary-label" style="color: #d97706;">POTONGAN BAGI HASIL MITRA</td>
                <td class="summary-value" style="color: #d97706;">- Rp {{ number_format($totalMitra, 0, ',', '.') }}</td>
            </tr>
            <tr class="summary-row summary-net">
                <td colspan="5" class="summary-label">PENDAPATAN BERSIH (NET PROFIT)</td>
                <td class="summary-value" style="color: #0891b2;">Rp {{ number_format($totalNet, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
        
        {{-- ================================================================ --}}
        {{-- 2. TABEL PEMBAYARAN KEUANGAN --}}
        {{-- ================================================================ --}}
        @elseif($reportType === 'pembayaran')
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Waktu Transaksi</th>
                <th width="25%">Identitas Transaksi</th>
                <th width="25%">Penyewa & Armada</th>
                <th width="15%">Tipe Transaksi</th>
                <th width="15%" class="text-right">Nominal Transaksi</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalGross = 0; 
                $totalRefund = 0;
                $totalSopir = 0; 
                $totalPajak = 0;
                $totalMitra = 0;
                $totalNet = 0; 
            @endphp
            @forelse($data as $index => $row)
                @php
                    $jumlah = $row->jumlah ?? 0;
                    $tipe = strtolower(trim($row->tipe_transaksi));
                    
                    if($tipe === 'denda') {
                        $totalGross += $jumlah;
                        $totalNet += $jumlah; // Denda tidak kena pajak/bagi hasil
                        
                    } elseif ($tipe === 'refund') {
                        $totalRefund += $jumlah;
                        $totalNet -= $jumlah; 
                        
                    } else {
                        // Normal payment (DP / Lunas / Sisa)
                        $peminjaman = $row->peminjaman;
                        if($peminjaman) {
                            $totalHarga = $peminjaman->total_harga > 0 ? $peminjaman->total_harga : 1;
                            $ratio = $jumlah / $totalHarga; 
                            
                            $biayaSopir = 0;
                            if($peminjaman->sopir_id) {
                                $days = max(1, \Carbon\Carbon::parse($peminjaman->tanggal_sewa)->diffInDays(\Carbon\Carbon::parse($peminjaman->tanggal_kembali)));
                                $biayaSopir = (150000 * $days) * $ratio;
                            }
                            
                            $sewaMurni = max(0, $jumlah - $biayaSopir);
                            $pendapatanBersih = round($sewaMurni / 1.11);
                            $pajak = $sewaMurni - $pendapatanBersih;
                            
                            $hakRental = $pendapatanBersih;
                            $hakMitra = 0;
                            
                            if($peminjaman->mobil && $peminjaman->mobil->status_kepemilikan === 'mitra') {
                                $persenRental = $peminjaman->mobil->persentase_bagi_hasil_rental ?? 100;
                                $hakRental = round($pendapatanBersih * ($persenRental / 100));
                                $hakMitra = $pendapatanBersih - $hakRental;
                            }
                            
                            $totalSopir += $biayaSopir;
                            $totalPajak += $pajak;
                            $totalMitra += $hakMitra;
                            $totalNet += $hakRental;
                        }
                        $totalGross += $jumlah;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, H:i') }}</td>
                    <td>{{ $row->id_transaksi_midtrans }}</td>
                    <td>
                        {{ $row->peminjaman->user->name ?? 'N/A' }}<br>
                        <small style="color: #666;">{{ $row->peminjaman->mobil->merek ?? '-' }} ({{ $row->peminjaman->mobil_id ?? '-' }})</small>
                    </td>
                    <td class="text-center">{{ strtoupper($row->tipe_transaksi) }}</td>
                    <td class="text-right">
                        @if($tipe === 'refund')
                            <span style="color: red;">- Rp {{ number_format($jumlah, 0, ',', '.') }}</span>
                        @else
                            Rp {{ number_format($jumlah, 0, ',', '.') }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Tidak ada data yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($data) > 0)
        <tfoot>
            <tr class="summary-row" style="background-color: #f8fafc;">
                <td colspan="5" class="summary-label">TOTAL PENERIMAAN KESELURUHAN (KOTOR)</td>
                <td class="summary-value">Rp {{ number_format($totalGross, 0, ',', '.') }}</td>
            </tr>
            @if($totalRefund > 0)
            <tr class="summary-row">
                <td colspan="5" class="summary-label" style="color: #dc2626;">PENGELUARAN PENGEMBALIAN DANA (REFUND)</td>
                <td class="summary-value" style="color: #dc2626;">- Rp {{ number_format($totalRefund, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="summary-row">
                <td colspan="5" class="summary-label" style="color: #059669;">POTONGAN PENDAPATAN SOPIR</td>
                <td class="summary-value" style="color: #059669;">- Rp {{ number_format($totalSopir, 0, ',', '.') }}</td>
            </tr>
            <tr class="summary-row">
                <td colspan="5" class="summary-label" style="color: #dc2626;">POTONGAN PAJAK PPN (11%)</td>
                <td class="summary-value" style="color: #dc2626;">- Rp {{ number_format($totalPajak, 0, ',', '.') }}</td>
            </tr>
            <tr class="summary-row">
                <td colspan="5" class="summary-label" style="color: #d97706;">POTONGAN BAGI HASIL MITRA</td>
                <td class="summary-value" style="color: #d97706;">- Rp {{ number_format($totalMitra, 0, ',', '.') }}</td>
            </tr>
            <tr class="summary-row summary-net">
                <td colspan="5" class="summary-label">PENDAPATAN BERSIH (NET PROFIT)</td>
                <td class="summary-value" style="color: #0891b2;">Rp {{ number_format($totalNet, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
        
        {{-- ================================================================ --}}
        {{-- 3. TABEL DENDA --}}
        {{-- ================================================================ --}}
        @elseif($reportType === 'denda')
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal Pembayaran</th>
                <th width="20%">Penyewa & Armada</th>
                <th width="30%">Keterangan Denda</th>
                <th width="15%">Status Pembayaran</th>
                <th width="15%" class="text-right">Nominal Denda</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($data as $index => $row)
                @php
                    if (strtolower($row->status_denda) === 'sudah dibayar') {
                        $grandTotal += $row->total_denda;
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $row->tanggal_pembayaran_denda ? \Carbon\Carbon::parse($row->tanggal_pembayaran_denda)->format('d/m/Y') : '-' }}</td>
                    <td>
                        {{ $row->peminjaman->user->name ?? '-' }}<br>
                        <small style="color: #666;">{{ $row->peminjaman->mobil->merek ?? '-' }} ({{ $row->peminjaman->mobil_id ?? '-' }})</small>
                    </td>
                    <td><small>{{ $row->keterangan_denda ?? '-' }}</small></td>
                    <td class="text-center">{{ ucfirst(str_replace('_', ' ', $row->status_denda)) }}</td>
                    <td class="text-right">Rp {{ number_format($row->total_denda, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px;">Tidak ada data yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($data) > 0)
        <tfoot>
            <tr class="summary-row summary-net">
                <td colspan="5" class="summary-label">TOTAL DENDA DIBAYARKAN (NET)</td>
                <td class="summary-value" style="color: #0891b2;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
        @endif
        
        @endif 
    </table>

    {{-- BAGIAN TANDA TANGAN --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td width="70%" style="vertical-align: bottom; font-size: 10px; color: #666;">
                    * Dicetak oleh sistem pada {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }}
                </td>
                
                <td class="signature-box">
                    <p>Sleman, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</p>
                    <p>Mengetahui,</p>
                    <div class="signature-space"></div>
                    <p style="font-weight: bold; text-decoration: underline;">{{ Auth::user()->name ?? 'Owner' }}</p>
                    <p style="margin-top: 2px;">Owner</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>