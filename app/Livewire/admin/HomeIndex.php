<?php

namespace App\Livewire\admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Models\Mobil;
use App\Models\Peminjaman;
use App\Models\PaymentTransaction;
use App\Models\UserIdentification;
use App\Models\Fine; // Import Model Fine (Denda)
use Barryvdh\DomPDF\Facade\Pdf; 

class Homeindex extends Component
{
    public $showExportModal = false;
    public $dateStart, $dateEnd, $filterStatusExport = ''; 

    #[Layout('layouts.admin')] 
    public function render()
    { 
        // 1. DATA KARTU STATISTIK (Sama seperti sebelumnya)
        $totalPendapatan = PaymentTransaction::where('status', 'settlement')->sum('amount');
        $totalMobil = Mobil::count();
        $mobilTersedia = Mobil::where('status', 'tersedia')->count();
        $mobilDisewa = Mobil::where('status', 'disewa')->count();
        $transaksiAktif = Peminjaman::where('status', 'berlangsung')->count();
        $pendingVerifikasi = UserIdentification::where('status_approval', 'menunggu')->count();

        // 2. GRAFIK PENDAPATAN BULANAN (Area Chart)
        $monthlyRevenue = PaymentTransaction::select(
            DB::raw('SUM(amount) as total'),
            DB::raw('MONTH(created_at) as month')
        )
        ->where('status', 'settlement')
        ->whereYear('created_at', date('Y'))
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('total', 'month')
        ->toArray();

        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = $monthlyRevenue[$i] ?? 0;
        }

        // 3. GRAFIK DENDA BULANAN (Bar Chart) - BARU!
        $monthlyFines = Fine::select(
            DB::raw('SUM(total_denda) as total'), // Sesuaikan nama kolom denda di tabel 'fines'
            DB::raw('MONTH(created_at) as month')
        )
        ->whereYear('created_at', date('Y'))
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('total', 'month')
        ->toArray();

        $fineData = [];
        for ($i = 1; $i <= 12; $i++) {
            $fineData[] = $monthlyFines[$i] ?? 0;
        }

        // 4. GRAFIK MOBIL TERLARIS (Donut Chart) - BARU!
        // Menghitung berapa kali mobil_id muncul di tabel peminjaman
        $topMobils = Peminjaman::select('mobil_id', DB::raw('count(*) as total'))
            ->groupBy('mobil_id')
            ->orderByDesc('total')
            ->take(5) // Ambil Top 5
            ->get();
        
        $topMobilLabels = [];
        $topMobilData = [];

        // Ambil nama/plat mobil manual karena mobil_id di peminjaman adalah string (Plat Nomor)
        // Kita perlu query ke tabel mobils untuk dapat Merek/Tipe jika mau lebih detail,
        // tapi pakai ID (Plat) saja sudah cukup representatif.
        foreach ($topMobils as $item) {
            // Optional: Ambil detail merek jika mau
            $mobilDetail = Mobil::find($item->mobil_id);
            $label = $mobilDetail ? $mobilDetail->merek . ' ' . $mobilDetail->tipe : $item->mobil_id;
            
            $topMobilLabels[] = $label;
            $topMobilData[] = $item->total;
        }

        // 5. TRANSAKSI TERBARU
        $recentTransactions = Peminjaman::with('user', 'mobil')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('livewire.admin.home-index', [
            'totalPendapatan' => $totalPendapatan,
            'totalMobil' => $totalMobil,
            'mobilTersedia' => $mobilTersedia,
            'mobilDisewa' => $mobilDisewa,
            'transaksiAktif' => $transaksiAktif,
            'pendingVerifikasi' => $pendingVerifikasi,
            'chartData' => $chartData,
            'fineData' => $fineData, // Data Denda
            'topMobilLabels' => $topMobilLabels, // Label Donut
            'topMobilData' => $topMobilData, // Data Donut
            'recentTransactions' => $recentTransactions
        ]);
    }

    // ... (Fungsi Export PDF tetap sama) ...
    public function openExportModal() { $this->reset(['dateStart', 'dateEnd', 'filterStatusExport']); $this->dateStart = date('Y-m-01'); $this->dateEnd = date('Y-m-d'); $this->showExportModal = true; }
    public function downloadReport() {
        $this->validate(['dateStart' => 'required|date', 'dateEnd' => 'required|date|after_or_equal:dateStart']);
        $data = Peminjaman::with(['user', 'mobil'])->whereBetween('tanggal_sewa', [$this->dateStart, $this->dateEnd])->when($this->filterStatusExport, function($q) { $q->where('status', $this->filterStatusExport); })->orderBy('tanggal_sewa', 'asc')->get();
        $pdf = Pdf::loadView('reports.transaksi_pdf', ['data' => $data, 'startDate' => $this->dateStart, 'endDate' => $this->dateEnd, 'totalOmzet' => $data->sum('total_harga')]);
        $pdf->setPaper('a4', 'landscape'); $this->showExportModal = false;
        return response()->streamDownload(function () use ($pdf) { echo $pdf->output(); }, 'Laporan-Rental-' . date('d-m-Y-His') . '.pdf');
    }
}