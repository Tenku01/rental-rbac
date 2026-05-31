<?php

namespace App\Livewire\Menu\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

use App\Models\{User, Mobil, Peminjaman, Pengembalian, PembatalanPesanan, TransaksiPembayaran, Sopir, InspeksiMobil};
use Spatie\Permission\Models\Role;

class HomeIndex extends Component
{
    // Property untuk Modal Export
    public $showExportModal = false;
    public $exportType = 'peminjaman'; 
    public $exportStartDate;
    public $exportEndDate;
    public $exportStatus = 'all';

    // Property untuk tracking permission
    public $hasFinancialAccess = false;
    public $hasOperationalAccess = false;
    public $hasDriverAccess = false;
    public $hasSystemAccess = false;

    #[Layout('layouts.admin')]
    public function mount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user) {
            $this->hasFinancialAccess = Gate::any([
                'read-transaksi_pembayaran',
                'read-peminjaman',
                'read-pembatalan_pesanan'
            ]);

            $this->hasOperationalAccess = Gate::any([
                'read-mobil',
                'read-inspeksi_mobil',
                'read-identitas_pengguna'
            ]);

            $this->hasDriverAccess = Gate::any([
                'read-logbook_sopir'
            ]);

            $this->hasSystemAccess = Gate::any([
                'read-users'
            ]);
        }

        // Set default date untuk modal export (Bulan ini)
        $this->exportStartDate = now()->startOfMonth()->format('Y-m-d');
        $this->exportEndDate = now()->format('Y-m-d');
    }

    // Fungsi untuk mereset dan membuka modal
    public function openExportModal()
    {
        $this->exportType = 'peminjaman';
        $this->exportStatus = 'all';
        $this->showExportModal = true;
    }

    // Fungsi Utama untuk Download Laporan
    public function downloadReport()
    {
        // Validasi input tanggal
        $this->validate([
            'exportStartDate' => 'required|date',
            'exportEndDate' => 'required|date|after_or_equal:exportStartDate',
            'exportType' => 'required|in:peminjaman,pembayaran,denda',
        ]);

        $data = [];
        $title = '';

        $start = Carbon::parse($this->exportStartDate)->startOfDay();
        $end = Carbon::parse($this->exportEndDate)->endOfDay();

        // 1. Logika Laporan Peminjaman (Operasional)
        if ($this->exportType === 'peminjaman') {
            $title = 'LAPORAN OPERASIONAL PEMINJAMAN';
            $query = Peminjaman::with(['user', 'mobil', 'sopir.user', 'pengembalian'])
                ->whereBetween('tanggal_sewa', [$start, $end]);

            if ($this->exportStatus !== 'all') {
                $query->where('status', $this->exportStatus);
            }

            $data = $query->orderBy('tanggal_sewa', 'desc')->get();
        }

        // 2. Logika Laporan Pembayaran (Cashflow/Keuangan)
        elseif ($this->exportType === 'pembayaran') {
            $title = 'LAPORAN TRANSAKSI KEUANGAN';
            $query = TransaksiPembayaran::with(['peminjaman.user', 'peminjaman.mobil', 'peminjaman.sopir.user'])
                ->whereBetween('created_at', [$start, $end]);

            if ($this->exportStatus !== 'all') {
                $query->where('status', $this->exportStatus);
            }

            $data = $query->orderBy('created_at', 'desc')->get();
        }

        // 3. Logika Laporan Denda (Menggunakan tabel Pengembalian)
        elseif ($this->exportType === 'denda') {
            $title = 'LAPORAN PENERIMAAN DENDA';
            $query = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil', 'pemeriksa'])
                ->where('total_denda', '>', 0)
                ->whereBetween('updated_at', [$start, $end]);

            if ($this->exportStatus !== 'all') {
                $query->where('status_denda', $this->exportStatus);
            }

            $data = $query->orderBy('updated_at', 'desc')->get();
        }

        // Generate PDF
       $pdf = Pdf::loadView('reports.transaksi_pdf', [
            'data' => $data,
            'startDate' => $this->exportStartDate,
            'endDate' => $this->exportEndDate,
            'reportType' => $this->exportType,
            'title' => $title
        ]);

        $this->showExportModal = false;

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, strtolower(str_replace(' ', '_', $title)) . '_' . date('Ymd_Hi') . '.pdf');
    }

    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $this->initializeDefaultData();

        if (!$user) return view('livewire.menu.dashboard.home-index', $data);

        // --- 1. SEKSI KEUANGAN & TRANSAKSI ---
        if ($this->hasFinancialAccess) {
            if (Gate::allows('read-transaksi_pembayaran')) {
                $this->calculateNetFinancials($data);
            }

            if (Gate::allows('read-peminjaman')) {
                $data['peminjamanBerlangsung'] = Peminjaman::where('status', 'berlangsung')->count();
                $data['peminjamanBaru'] = Peminjaman::where('status', 'menunggu pembayaran')->count();
                $data['peminjamanSelesai'] = Peminjaman::where('status', 'selesai')->count();
                $data['recentTransactions'] = Peminjaman::with(['user', 'mobil'])->latest()->take(5)->get();
                $this->getTopMobilData($data);
            }

            if (Gate::allows('read-pembatalan_pesanan')) {
                $data['totalPembatalan'] = PembatalanPesanan::count();
                $data['pendingPembatalan'] = PembatalanPesanan::where('status_persetujuan', 'pending')->count();
            }
        }

        // --- 2. SEKSI OPERASIONAL ---
        if ($this->hasOperationalAccess) {
            if (Gate::allows('read-mobil')) {
                $data['totalMobil'] = Mobil::count();
                $data['mobilTersedia'] = Mobil::where('status', 'tersedia')->count();
                $data['mobilDisewa'] = Mobil::where('status', 'disewa')->count();
            }

            if (Gate::allows('read-identitas_pengguna')) {
                $data['pendingVerifikasi'] = User::where('status_verifikasi', 'menunggu')->count();
            }

            if (Gate::allows('read-inspeksi_mobil')) {
                $data['latestChecks'] = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
                    ->orderByDesc('tanggal_pengembalian')
                    ->take(5)->get();

                $data['metrics'] = [
                    ['label' => 'Perlu Pengecekan', 'value' => Pengembalian::where('status', 'menunggu pengecekan')->count(), 'color' => 'yellow'],
                    // Karena InspeksiMobil sudah digabung, kita hitung yang pemeriksa_id nya tidak null
                    ['label' => 'Total Inspeksi', 'value' => Pengembalian::whereNotNull('pemeriksa_id')->count(), 'color' => 'green']
                ];
            }
        }

        // --- 3. SEKSI SOPIR ---
        if ($this->hasDriverAccess && Gate::allows('read-logbook_sopir')) {
            $sopirModel = Sopir::where('user_id', $user->id)->first();
            $data['sopir'] = $sopirModel;

            $queryTugas = Peminjaman::with(['mobil', 'user'])->whereIn('status', ['sudah dibayar lunas', 'berlangsung']);

            if (!$user->hasRole('admin')) {
                $queryTugas->where('sopir_id', $sopirModel->id ?? 0);
            }

            $data['tugasAktif'] = $queryTugas->get();
        }

        // --- 4. SISTEM ---
        if ($this->hasSystemAccess && Gate::allows('read-users')) {
            $data['totalPelanggan'] = User::role('pelanggan')->count();

            $operationalRoles = Role::whereNotIn('name', ['admin', 'pelanggan'])->pluck('name');
            $data['operationalCount'] = User::role($operationalRoles)->count();
        }

        $data['hasFinancialAccess'] = $this->hasFinancialAccess;
        $data['hasOperationalAccess'] = $this->hasOperationalAccess;
        $data['hasDriverAccess'] = $this->hasDriverAccess;
        $data['hasSystemAccess'] = $this->hasSystemAccess;

        return view('livewire.menu.dashboard.home-index', $data);
    }

    private function calculateNetFinancials(&$data)
    {
        $currentYear = date('Y');
        
        // 1. Ambil Transaksi Sewa yang sukses
        $payments = TransaksiPembayaran::with(['peminjaman.mobil'])
            ->whereYear('created_at', $currentYear)
            ->whereIn('status', ['success', 'settlement'])
            ->whereIn('tipe_transaksi', ['dp', 'sisa', 'lunas'])
            ->get();

        $pendapatanBersihTotal = 0;
        $monthlyNet = array_fill(1, 12, 0);

        foreach ($payments as $pay) {
            $jumlah = $pay->jumlah;
            $peminjaman = $pay->peminjaman;
            $net = 0;
            
            if ($peminjaman && $peminjaman->mobil) {
                // Estimasi Biaya Sopir (Jika Ada Sopir) -> Misal: Rp 150.000 / Hari
                $days = max(1, Carbon::parse($peminjaman->tanggal_sewa)->diffInDays(Carbon::parse($peminjaman->tanggal_kembali)));
                $biayaSopir = $peminjaman->sopir_id ? (150000 * $days) : 0;
                
                // Proporsi Jika Bayar DP
                $ratio = $peminjaman->total_harga > 0 ? ($jumlah / $peminjaman->total_harga) : 1;
                $sopirProporsional = $biayaSopir * $ratio;
                
                // Pendapatan Murni Setelah Sopir
                $sewaMurni = max(0, $jumlah - $sopirProporsional);
                
                // Potong PPN 11%
                $net = round($sewaMurni / 1.11); 
                
                // Potong Bagi Hasil Mitra
                if ($peminjaman->mobil->status_kepemilikan === 'mitra') {
                    $persen = $peminjaman->mobil->persentase_bagi_hasil_rental ?? 100;
                    $net = round($net * ($persen / 100));
                }
            } else {
                // Fallback jika data peminjaman tidak lengkap
                $net = round($jumlah / 1.11); 
            }
            
            $pendapatanBersihTotal += $net;
            $month = (int) Carbon::parse($pay->created_at)->format('n');
            $monthlyNet[$month] += $net;
        }

        // 2. Ambil Transaksi Denda dari tabel Pengembalian
        $dendas = Pengembalian::where('status_denda', 'sudah dibayar')
            ->whereYear('tanggal_pembayaran_denda', $currentYear)
            ->get();
            
        $monthlyDenda = array_fill(1, 12, 0);
        $dendaTotal = 0;
        
        foreach ($dendas as $d) {
            $jumlah = $d->total_denda;
            $month = (int) Carbon::parse($d->tanggal_pembayaran_denda)->format('n');
            $monthlyDenda[$month] += $jumlah;
            $dendaTotal += $jumlah;
        }

        // 3. Kurangi dengan Refund
        $refunds = TransaksiPembayaran::where('tipe_transaksi', 'refund')
            ->whereYear('created_at', $currentYear)
            ->get();
            
        foreach($refunds as $ref) {
            $pendapatanBersihTotal -= $ref->jumlah;
            $month = (int) Carbon::parse($ref->created_at)->format('n');
            $monthlyNet[$month] -= $ref->jumlah;
        }

        // 4. Masukkan ke Array Data View
        $data['totalPendapatan'] = max(0, $pendapatanBersihTotal + $dendaTotal);
        $data['revenueData'] = array_values($monthlyNet);
        $data['fineData'] = array_values($monthlyDenda);
        
        $combinedData = [];
        for($i = 0; $i < 12; $i++){
            $combinedData[] = $monthlyNet[$i + 1] + $monthlyDenda[$i + 1];
        }
        $data['combinedData'] = $combinedData;
    }

    private function getTopMobilData(&$data)
    {
        $top = Peminjaman::select('mobil_id', DB::raw('count(*) as total'))
            ->groupBy('mobil_id')->orderByDesc('total')->take(5)->get();
        $data['topMobilLabels'] = Mobil::whereIn('id', $top->pluck('mobil_id'))->pluck('merek')->toArray();
        $data['topMobilData'] = $top->pluck('total')->toArray();
    }

    private function initializeDefaultData()
    {
        return [
            'totalPendapatan' => 0,
            'totalMobil' => 0,
            'mobilTersedia' => 0,
            'mobilDisewa' => 0,
            'peminjamanBerlangsung' => 0,
            'peminjamanBaru' => 0,
            'totalPelanggan' => 0,
            'totalPembatalan' => 0,
            'pendingPembatalan' => 0,
            'peminjamanSelesai' => 0,
            'pendingVerifikasi' => 0,
            'operationalCount' => 0,
            'recentTransactions' => collect(),
            'recentPeminjaman' => collect(),
            'revenueData' => [],
            'fineData' => [],
            'combinedData' => [],
            'topMobilLabels' => [],
            'topMobilData' => [],
            'latestChecks' => collect(),
            'metrics' => [],
            'tugasAktif' => collect(),
            'sopir' => null,
            'hasFinancialAccess' => false,
            'hasOperationalAccess' => false,
            'hasDriverAccess' => false,
            'hasSystemAccess' => false
        ];
    }
}