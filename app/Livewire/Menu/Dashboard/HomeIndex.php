<?php

namespace App\Livewire\Menu\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Barryvdh\DomPDF\Facade\Pdf; // Tambahkan ini untuk Export PDF

// Model disesuaikan dengan bahasa Indonesia sesuai perombakan database
use App\Models\{User, Mobil, Peminjaman, Pengembalian, PembatalanPesanan, TransaksiPembayaran, Denda, Sopir, InspeksiMobil};
use Spatie\Permission\Models\Role;

class HomeIndex extends Component
{
    // Property untuk Modal Export
    public $showExportModal = false;
    public $exportType = 'peminjaman'; // peminjaman, pembayaran, denda
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
                'read-payment_transactions', 
                'read-peminjaman',
                'read-pembatalan_pesanan'
            ]);
            
            $this->hasOperationalAccess = Gate::any([
                'read-mobils',
                'read-vehicle_inspections',
                'read-user_identifications'
            ]);
            
            $this->hasDriverAccess = Gate::any([
                'read-driver_logbooks'
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
        $totalOmzet = 0;
        $title = '';

        // 1. Logika Laporan Peminjaman (Operasional)
        if ($this->exportType === 'peminjaman') {
            $title = 'LAPORAN OPERASIONAL PEMINJAMAN';
            $query = Peminjaman::with(['user', 'mobil'])
                ->whereBetween('tanggal_sewa', [$this->exportStartDate, $this->exportEndDate]);

            if ($this->exportStatus !== 'all') {
                $query->where('status', $this->exportStatus);
            }

            $data = $query->orderBy('tanggal_sewa', 'desc')->get();
            $totalOmzet = $data->sum('total_harga');
        } 
        
        // 2. Logika Laporan Pembayaran (Cashflow/Keuangan)
        elseif ($this->exportType === 'pembayaran') {
            $title = 'LAPORAN TRANSAKSI KEUANGAN';
            $query = TransaksiPembayaran::with(['peminjaman.user', 'peminjaman.mobil'])
                ->whereBetween('created_at', [$this->exportStartDate . ' 00:00:00', $this->exportEndDate . ' 23:59:59']);

            if ($this->exportStatus !== 'all') {
                $query->where('status', $this->exportStatus);
            }

            $data = $query->orderBy('created_at', 'desc')->get();
            // Hanya hitung status sukses/settlement
            $totalOmzet = $data->whereIn('status', ['success', 'settlement'])->sum('jumlah');
        } 
        
        // 3. Logika Laporan Denda
        elseif ($this->exportType === 'denda') {
            $title = 'LAPORAN PENERIMAAN DENDA';
            $query = Denda::with(['peminjaman.user', 'peminjaman.mobil'])
                ->whereBetween('tanggal_terdeteksi', [$this->exportStartDate, $this->exportEndDate]);

            if ($this->exportStatus !== 'all') {
                $query->where('status', $this->exportStatus);
            }

            $data = $query->orderBy('tanggal_terdeteksi', 'desc')->get();
            $totalOmzet = $data->where('status', 'sudah dibayar')->sum('total_denda');
        }

        // Generate PDF
        $pdf = Pdf::loadView('reports.transaksi_pdf', [
            'data' => $data,
            'totalOmzet' => $totalOmzet,
            'startDate' => $this->exportStartDate,
            'endDate' => $this->exportEndDate,
            'reportType' => $this->exportType,
            'title' => $title
        ]);

        // Menutup modal setelah tombol download ditekan
        $this->showExportModal = false;

        // Return response download ke browser
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'Laporan_' . ucfirst($this->exportType) . '_' . date('Ymd_Hi') . '.pdf');
    }
    
    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $data = $this->initializeDefaultData();
        
        if (!$user) return view('livewire.menu.dashboard.home-index', $data);
        
        // --- 1. SEKSI KEUANGAN & TRANSAKSI ---
        if ($this->hasFinancialAccess) {
            if (Gate::allows('read-payment_transactions')) {
                $data['totalPendapatan'] = TransaksiPembayaran::where('status', 'settlement')->sum('jumlah');
                $data['chartData'] = $this->getMonthlyRevenue();
                $data['fineData'] = $this->getMonthlyFines();
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
            if (Gate::allows('read-mobils')) {
                $data['totalMobil'] = Mobil::count();
                $data['mobilTersedia'] = Mobil::where('status', 'tersedia')->count();
                $data['mobilDisewa'] = Mobil::where('status', 'disewa')->count();
            }
            
            if (Gate::allows('read-user_identifications')) {
                $data['pendingVerifikasi'] = User::where('status_verifikasi', 'menunggu')->count();
            }
            
            if (Gate::allows('read-vehicle_inspections')) {
                $data['latestChecks'] = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
                    ->orderByDesc('tanggal_pengembalian')
                    ->take(5)->get();
                
                $data['metrics'] = [
                    ['label' => 'Perlu Pengecekan', 'value' => Pengembalian::where('status', 'menunggu pengecekan')->count(), 'color' => 'yellow'],
                    ['label' => 'Total Inspeksi', 'value' => InspeksiMobil::count(), 'color' => 'green']
                ];
            }
        }
        
        // --- 3. SEKSI SOPIR ---
        if ($this->hasDriverAccess && Gate::allows('read-driver_logbooks')) {
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
    
    private function userCan($permission)
    {
        return Gate::allows($permission);
    }
    
    private function getMonthlyRevenue() {
        $rev = TransaksiPembayaran::where('status', 'settlement')->whereYear('created_at', date('Y'))
            ->select(DB::raw('SUM(jumlah) as total'), DB::raw('MONTH(created_at) as month'))
            ->groupBy('month')->pluck('total', 'month')->toArray();
        return array_map(fn($m) => $rev[$m] ?? 0, range(1, 12));
    }
    
    private function getMonthlyFines() {
        $fines = Denda::where('status', 'sudah dibayar')->whereYear('tanggal_pembayaran', date('Y'))
            ->select(DB::raw('SUM(total_denda) as total'), DB::raw('MONTH(tanggal_pembayaran) as month'))
            ->groupBy('month')->pluck('total', 'month')->toArray();
        return array_map(fn($m) => $fines[$m] ?? 0, range(1, 12));
    }
    
    private function getTopMobilData(&$data) {
        $top = Peminjaman::select('mobil_id', DB::raw('count(*) as total'))
            ->groupBy('mobil_id')->orderByDesc('total')->take(5)->get();
        $data['topMobilLabels'] = Mobil::whereIn('id', $top->pluck('mobil_id'))->pluck('merek')->toArray();
        $data['topMobilData'] = $top->pluck('total')->toArray();
    }
    
    private function initializeDefaultData() {
        return [
            'totalPendapatan' => 0, 'totalMobil' => 0, 'mobilTersedia' => 0, 'mobilDisewa' => 0,
            'peminjamanBerlangsung' => 0, 'peminjamanBaru' => 0, 'totalPelanggan' => 0,
            'totalPembatalan' => 0, 'pendingPembatalan' => 0, 'peminjamanSelesai' => 0,
            'pendingVerifikasi' => 0, 'operationalCount' => 0, 'recentTransactions' => collect(), 
            'recentPeminjaman' => collect(), 'chartData' => [], 'fineData' => [], 
            'topMobilLabels' => [], 'topMobilData' => [], 'latestChecks' => collect(),
            'metrics' => [], 'tugasAktif' => collect(), 'sopir' => null,
            'hasFinancialAccess' => false,
            'hasOperationalAccess' => false,
            'hasDriverAccess' => false,
            'hasSystemAccess' => false
        ];
    }
}