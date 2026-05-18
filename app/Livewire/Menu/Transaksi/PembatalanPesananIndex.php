<?php

namespace App\Livewire\Menu\Transaksi;

use App\Models\Mobil;
use App\Models\PembatalanPesanan;
use App\Models\Peminjaman;
use App\Models\TransaksiPembayaran; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class PembatalanPesananIndex extends Component
{
    use WithPagination;

    // --- Filters & Search ---
    public $search = '';
    public $filterStatus = ''; 

    // --- Modal States ---
    public $showCreateModal = false;
    public $showProcessModal = false; 
    public $showDetailModal = false;
    public $selectedPembatalan = null;

    // --- Form Fields ---
    public $processingId = null;
    public $peminjaman_id;
    public $alasan;
    public $persentase_refund = 100; // Mendukung desimal (76.2)
    
    // --- UI Helper Data ---
    public $estimasi_refund = 0;
    public $total_dibayarkan_customer = 0;

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        if (Gate::denies('read-pembatalan_pesanan')) {
            return redirect()->route('unauthorized');
        }
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    public function updated($propertyName)
    {
        if ($propertyName === 'peminjaman_id' || $propertyName === 'persentase_refund') {
            $this->calculateEstimation();
        }
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        abort_if(Gate::denies('read-pembatalan_pesanan'), 403, 'Akses ditolak.');

        $data = PembatalanPesanan::with(['peminjaman.user', 'peminjaman.mobil'])
            ->when($this->search, function($q) {
                $q->whereHas('peminjaman.user', fn($sq) => $sq->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhereHas('peminjaman', fn($sq) => $sq->where('id', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterStatus, fn($q) => $q->where('status_pengembalian_dana', $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate(10)->withPath(url()->current());

        $cancellableTransactions = Peminjaman::with(['user', 'mobil'])
            ->whereNotIn('status', ['dibatalkan', 'ditolak', 'selesai'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.menu.transaksi.pembatalan-index', [
            'pembatalan' => $data,
            'cancellable_transactions' => $cancellableTransactions
        ]);
    }

    // =========================================================================
    // CORE LOGIC: EKSEKUSI REFUND KE MIDTRANS / MANUAL (ANTI-GAGAL)
    // =========================================================================

      private function executeRefund($peminjaman, $persentase, $alasan)
    {
        $decimalPercentage = (float) $persentase / 100;
        $totalTargetRefund = (float) $peminjaman->total_dibayarkan * $decimalPercentage;

        if ($totalTargetRefund <= 0) {
            return ['status' => 'no_refund', 'jumlah' => 0, 'refund_id' => null, 'all_failed' => false];
        }

        $payments = TransaksiPembayaran::where('peminjaman_id', $peminjaman->id)
            ->whereIn('status', ['settlement', 'capture', 'success', 'paid'])
            ->where('tipe_transaksi', '!=', 'refund')
            ->get();

        if ($payments->isEmpty()) {
            return ['status' => 'no_refund', 'jumlah' => 0, 'refund_id' => null, 'all_failed' => false];
        }

        $totalRefundedNow = 0;
        $refundIds = [];
        
        // 🔹 AMBIL CONFIG UNTUK DYNAMIC ENVIRONMENT
        $serverKey = config('services.midtrans.server_key');
        $isProduction = config('services.midtrans.is_production');
        $baseUrl = $isProduction ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';
        
        $sisaTargetRefund = $totalTargetRefund;
        $isAnyGateway = false;
        $allFailed = true; // Flag untuk mengecek apakah API menolak semua transaksi

        foreach ($payments as $payment) {
            if ($sisaTargetRefund <= 0) break;

            $amountToRefundFromThisTx = min((float) $payment->jumlah, $sisaTargetRefund);
            $refundKey = 'REF-' . time() . '-' . uniqid();
            $isManual = str_contains($payment->id_transaksi_midtrans, 'MANUAL');

            if ($isManual) {
                $refundData = [
                    'status_code' => '200',
                    'transaction_status' => 'refund',
                    'gross_amount' => $amountToRefundFromThisTx,
                    'order_id' => $payment->id_transaksi_midtrans,
                    'refund_key' => $refundKey,
                    'status_message' => 'Manual Refund Recorded'
                ];
                $refundStatusTransaksi = 'refunded';
                $allFailed = false;
            } else {
                $isAnyGateway = true;
                
                // 🔹 URL DINAMIS (Menggunakan $baseUrl)
                $refundUrl = "{$baseUrl}/v2/{$payment->id_transaksi_midtrans}/refund";

                try {
                    // 🔹 MENGGUNAKAN BASIC AUTH BAWAAN LARAVEL
                    $response = Http::withBasicAuth($serverKey, '')
                        ->withHeaders([
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ])->post($refundUrl, [
                            'refund_key' => $refundKey,
                            'amount' => $amountToRefundFromThisTx,
                            'reason' => $alasan ?? 'Pembatalan pesanan',
                        ]);
                    
                    $refundData = $response->json();
                    
                    // Jika gagal, set statusnya jadi 'failed' dan catat ke log
                    if (($refundData['status_code'] ?? '') != '200') {
                        $refundStatusTransaksi = 'failed';
                        Log::error('Refund Midtrans Gagal:', $refundData);
                    } else {
                        $refundStatusTransaksi = 'pending';
                        $allFailed = false;
                    }
                } catch (\Exception $e) {
                    $refundData = ['status_code' => '500', 'status_message' => $e->getMessage()];
                    $refundStatusTransaksi = 'failed';
                    Log::error('Error HTTP Refund Midtrans:', ['msg' => $e->getMessage()]);
                }
            }

            // Merekam apapun hasil dari Midtrans ke dalam database
            TransaksiPembayaran::create([
                'peminjaman_id' => $peminjaman->id,
                'id_transaksi_midtrans' => $refundKey,
                'status' => $refundStatusTransaksi,
                'jumlah' => $amountToRefundFromThisTx,
                'tipe_transaksi' => 'refund',
                'respon_midtrans' => json_encode($refundData),
                'id_transaksi_awal' => $payment->id,
            ]);

            $totalRefundedNow += $amountToRefundFromThisTx;
            $sisaTargetRefund -= $amountToRefundFromThisTx;
            $refundIds[] = $refundKey;
        }

        // Jika semua API menolak, status pembatalan dikembalikan jadi 'pending_refund'
        // agar admin ingat bahwa pelanggan belum nerima uang dan bisa ditransfer manual nanti.
        $statusPembatalan = $allFailed ? 'pending_refund' : ($isAnyGateway ? 'pending_refund' : 'refunded');

        return [
            'status' => $statusPembatalan, 
            'jumlah' => $totalTargetRefund, 
            'refund_id' => implode(', ', $refundIds),
            'all_failed' => $allFailed
        ];
    }

    // =========================================================================
    // ACTION 1: ADMIN MEMBATALKAN PESANAN SEPIHAK (INSTANT REFUND)
    // =========================================================================

    public function create()
    {
        abort_if(Gate::denies('create-pembatalan_pesanan'), 403);
        $this->resetForm();
        $this->persentase_refund = 100;
        $this->showCreateModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-pembatalan_pesanan'), 403);

        $this->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'alasan' => 'required|string|min:5',
            'persentase_refund' => 'required|numeric|min:0|max:100', 
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::findOrFail($this->peminjaman_id);

            // LANGSUNG TEMBAK KE MIDTRANS TANPA TAKUT ERROR DB ROLLBACK
            $refundResult = $this->executeRefund($peminjaman, $this->persentase_refund, $this->alasan);

            PembatalanPesanan::create([
                'peminjaman_id' => $peminjaman->id,
                'user_id' => $peminjaman->user_id,
                'dibatalkan_oleh' => 'admin', 
                'status_persetujuan' => 'approved', 
                'alasan' => $this->alasan,
                'status_pengembalian_dana' => $refundResult['status'], 
                'dibatalkan_pada' => now(), 
                'persentase_refund' => (float) $this->persentase_refund / 100, 
                'jumlah_refund' => $refundResult['jumlah'],
                'id_transaksi_refund' => $refundResult['refund_id'],
            ]);

            $peminjaman->update([
                'status' => 'dibatalkan',
                'sudah_refund' => $refundResult['jumlah'] > 0 ? 1 : 0
            ]);
            
            // Mobil ditarik ke daftar pemeliharaan
            if ($peminjaman->mobil) {
                Mobil::where('id', $peminjaman->mobil_id)->update(['status' => 'pemeliharaan']);
            }

            DB::commit();
            $this->closeModal();

            if ($refundResult['all_failed']) {
                $this->dispatch('notify', message: 'Pesanan batal, NAMUN Refund ditolak Gateway Midtrans! (Cek Riwayat Pembayaran).', type: 'warning');
            } elseif ($refundResult['jumlah'] > 0) {
                $this->dispatch('notify', message: 'Pesanan dibatalkan & Data Refund Rp '.number_format($refundResult['jumlah']).' dicatat.', type: 'success');
            } else {
                $this->dispatch('notify', message: 'Pesanan dibatalkan (0% Refund).', type: 'success');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Gagal: ' . $e->getMessage(), type: 'error');
        }
    }

    // =========================================================================
    // ACTION 2: ADMIN MEMPROSES PEMBATALAN DARI PELANGGAN
    // =========================================================================

    public function openProcessModal($id)
    {
        $this->resetForm();
        $this->selectedPembatalan = PembatalanPesanan::findOrFail($id);
        $this->processingId = $id;
        $this->peminjaman_id = $this->selectedPembatalan->peminjaman_id;
        
        $persentaseDb = (float) $this->selectedPembatalan->persentase_refund * 100;
        $this->persentase_refund = $persentaseDb > 0 ? $persentaseDb : 100;
        
        $this->calculateEstimation();
        $this->showProcessModal = true;
    }

    public function submitProcessRefund()
    {
        abort_if(Gate::denies('update-pembatalan_pesanan'), 403);
        $this->validate(['persentase_refund' => 'required|numeric|min:0|max:100']);

        DB::beginTransaction();
        try {
            $pembatalan = PembatalanPesanan::findOrFail($this->processingId);
            $peminjaman = Peminjaman::findOrFail($pembatalan->peminjaman_id);

            $refundResult = $this->executeRefund($peminjaman, $this->persentase_refund, $pembatalan->alasan);

            $pembatalan->update([
                'status_persetujuan' => 'approved', 
                'status_pengembalian_dana' => $refundResult['status'], 
                'persentase_refund' => (float) $this->persentase_refund / 100, 
                'jumlah_refund' => $refundResult['jumlah'],
                'id_transaksi_refund' => $refundResult['refund_id'],
            ]);

            if ($refundResult['jumlah'] > 0) {
                $peminjaman->update(['sudah_refund' => 1]);
            }

            if ($peminjaman->mobil) {
                Mobil::where('id', $peminjaman->mobil_id)->update(['status' => 'pemeliharaan']);
            }

            DB::commit();
            $this->closeModal();

            if ($refundResult['all_failed']) {
                $this->dispatch('notify', message: 'Telah disetujui, NAMUN Gateway menolak transfer (Cek Riwayat Pembayaran).', type: 'warning');
            } elseif ($refundResult['jumlah'] <= 0) {
                $this->dispatch('notify', message: 'Refund ditolak (0%). Pembatalan pesanan disetujui tanpa pengembalian dana.', type: 'warning');
            } else {
                $this->dispatch('notify', message: 'Persetujuan sukses! Dana Rp '.number_format($refundResult['jumlah']).' dicatat.', type: 'success');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Gagal memproses refund: ' . $e->getMessage(), type: 'error');
        }
    }

    public function showDetail($id)
    {
        $this->selectedPembatalan = PembatalanPesanan::with(['peminjaman.user', 'peminjaman.mobil'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function calculateEstimation()
    {
        if ($this->peminjaman_id) {
            $peminjaman = Peminjaman::find($this->peminjaman_id); 
            if ($peminjaman) {
                $this->total_dibayarkan_customer = (float) $peminjaman->total_dibayarkan; 
                $persen = (float) $this->persentase_refund / 100;
                $this->estimasi_refund = $this->total_dibayarkan_customer * $persen;
            }
        }
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showProcessModal = false;
        $this->showDetailModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['peminjaman_id', 'alasan', 'estimasi_refund', 'total_dibayarkan_customer', 'selectedPembatalan', 'processingId']);
        $this->persentase_refund = 100;
        $this->resetErrorBag();
    }
}