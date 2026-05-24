<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'Laporan Transaksi' }}</title>
    <style>
        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            font-size: 11px; 
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
            font-family: 'Times New Roman', Times, serif; /* Font resmi untuk kop surat */
        }
        .teks-kop h1 {
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: bold;
            color: #000; /* Hitam tegas sesuai gambar */
            letter-spacing: 0.5px;
        }
        .teks-kop p {
            margin: 5px 0;
            font-size: 15px;
            color: #000;
            font-weight: bold; /* Teks di gambar terlihat tebal */
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
            padding: 8px 6px; 
            text-align: left; 
        }
        table.data-table th { 
            background-color: #f1f5f9; 
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        
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
                        // Membaca gambar dari folder public dan mengubahnya ke base64 agar aman di-render oleh DOMPDF
                        $imagePath = public_path('logoakarentcar.png');
                        $logoData = '';
                        if(file_exists($imagePath)){
                            $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($imagePath));
                        }
                    ?>
                    @if($logoData)
                        <img src="{{ $logoData }}" style="max-width: 90px; height: auto;">
                    @else
                        <!-- Fallback jika gambar tidak ditemukan -->
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
        {{-- MENGGUNAKAN locale('id')->isoFormat() AGAR PASTI BAHASA INDONESIA --}}
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->locale('id')->isoFormat('D MMMM Y') }} - {{ \Carbon\Carbon::parse($endDate)->locale('id')->isoFormat('D MMMM Y') }}</p>
    </div>

    {{-- TABEL DATA DINAMIS --}}
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                
                @if($reportType === 'peminjaman')
                    <th width="20%">Periode Sewa</th>
                    <th width="20%">Penyewa</th>
                    <th width="20%">Armada</th>
                    <th width="15%">Status</th>
                    <th width="20%" class="text-right">Total Biaya</th>
                
                @elseif($reportType === 'pembayaran')
                    <th width="15%">Tgl Bayar</th>
                    <th width="20%">ID Transaksi</th>
                    <th width="20%">Penyewa</th>
                    <th width="10%">Tipe</th>
                    <th width="15%">Status</th>
                    <th width="15%" class="text-right">Nominal</th>
                
                @elseif($reportType === 'denda')
                    <th width="15%">Tgl Terdeteksi</th>
                    <th width="20%">Penyewa / Armada</th>
                    <th width="25%">Keterangan</th>
                    <th width="15%">Status Bayar</th>
                    <th width="20%" class="text-right">Total Denda</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotal = 0;
            @endphp

            @forelse($data as $index => $row)
                @php
                    if ($reportType === 'pembayaran') {
                        if (strtolower($row->tipe_transaksi) === 'refund') {
                            $grandTotal -= $row->jumlah;
                        } elseif (in_array(strtolower($row->status), ['success', 'settlement'])) {
                            $grandTotal += $row->jumlah;
                        }
                    } elseif ($reportType === 'peminjaman') {
                        $grandTotal += $row->total_harga;
                    } elseif ($reportType === 'denda') {
                        if (strtolower($row->status) === 'sudah dibayar') {
                            $grandTotal += $row->total_denda;
                        }
                    }
                @endphp

                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    
                    @if($reportType === 'peminjaman')
                        <td>
                            {{ \Carbon\Carbon::parse($row->tanggal_sewa)->format('d/m/Y') }}<br>
                            <small>s/d {{ \Carbon\Carbon::parse($row->tanggal_kembali)->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            {{ $row->user->name ?? '-' }}<br>
                            <small style="color: #666;">{{ $row->user->email ?? '' }}</small>
                        </td>
                        <td>
                            {{ $row->mobil->merek ?? '-' }} {{ $row->mobil->tipe ?? '' }}<br>
                            <small>({{ $row->mobil_id ?? '-' }})</small>
                        </td>
                        <td class="text-center">{{ ucfirst($row->status) }}</td>
                        <td class="text-right">Rp {{ number_format($row->total_harga, 0, ',', '.') }}</td>
                    
                    @elseif($reportType === 'pembayaran')
                        <td class="text-center">{{ \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i') }}</td>
                        <td><small>{{ $row->id_transaksi_midtrans }}</small></td>
                        <td>{{ $row->peminjaman->user->name ?? '-' }}</td>
                        <td class="text-center">{{ strtoupper($row->tipe_transaksi) }}</td>
                        <td class="text-center">{{ ucfirst($row->status) }}</td>
                        <td class="text-right">
                            @if(strtolower($row->tipe_transaksi) === 'refund')
                                <span style="color: red;">- Rp {{ number_format($row->jumlah, 0, ',', '.') }}</span>
                            @else
                                Rp {{ number_format($row->jumlah, 0, ',', '.') }}
                            @endif
                        </td>

                    @elseif($reportType === 'denda')
                        <td class="text-center">{{ \Carbon\Carbon::parse($row->tanggal_terdeteksi)->format('d/m/Y') }}</td>
                        <td>
                            {{ $row->peminjaman->user->name ?? '-' }}<br>
                            <small style="color: #666;">{{ $row->peminjaman->mobil->merek ?? '-' }} ({{ $row->peminjaman->mobil_id ?? '-' }})</small>
                        </td>
                        <td><small>{{ $row->keterangan ?? '-' }}</small></td>
                        <td class="text-center">{{ ucfirst($row->status) }}</td>
                        <td class="text-right">Rp {{ number_format($row->total_denda, 0, ',', '.') }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $reportType === 'pembayaran' ? 7 : 6 }}" class="text-center" style="padding: 20px;">Tidak ada data yang ditemukan pada rentang tanggal dan filter ini.</td>
                </tr>
            @endforelse
        </tbody>
        
        {{-- FOOTER TABEL (TOTAL) --}}
        @if(count($data) > 0)
        <tfoot>
            <tr style="background-color: #f8fafc;">
                @php
                    $colspanTotal = 5; 
                    if ($reportType === 'pembayaran') {
                        $colspanTotal = 6; 
                    } elseif ($reportType === 'denda') {
                        $colspanTotal = 5; 
                    }
                @endphp
                
                <td colspan="{{ $colspanTotal }}" class="text-right" style="font-weight: bold; padding-right: 15px;">
                    TOTAL 
                    @if($reportType === 'peminjaman') NILAI TRANSAKSI
                    @elseif($reportType === 'pembayaran') PENERIMAAN BERSIH
                    @elseif($reportType === 'denda') DENDA TERBAYARKAN
                    @endif
                </td>
                
                <td class="text-right" style="font-weight: bold; color: #0891b2;">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- BAGIAN TANDA TANGAN --}}
    <div class="footer">
        <table class="footer-table">
            <tr>
                {{-- INFO TANGGAL DICETAK --}}
                <td width="70%" style="vertical-align: bottom; font-size: 10px; color: #666;">
                    * Dicetak oleh sistem pada {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y, HH:mm') }}
                </td>
                
                <td class="signature-box">
                    {{-- MENGGUNAKAN locale('id')->isoFormat() AGAR PASTI BAHASA INDONESIA --}}
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