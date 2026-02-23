<?php

namespace App\Livewire\Menu\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\PembatalanPesanan;
use App\Models\Peminjaman;
use App\Models\PaymentTransaction;
use App\Models\Mobil;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PembatalanPesananIndex extends Component
{
    use WithPagination;

    // --- Filters & Search ---
    public $search = '';
    public $filterStatus = ''; // pending_refund, refunded, no_refund_needed

    // --- Modal States ---
    public $showCreateModal = false;
    public $showDetailModal = false;
    public $isEditMode = false;
    public $selectedPembatalan = null;

    // --- Form Fields ---
    public $editingId = null;
    public $peminjaman_id;
    public $alasan;
    public $persentase_refund = 0; // Input 0 - 100
    
    // --- UI Helper Data ---
    public $estimasi_refund = 0;
    public $total_dibayarkan_customer = 0;

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    // Hitung estimasi real-time saat user pilih ID atau ketik persen
    public function updated($propertyName)
    {
        if ($propertyName === 'peminjaman_id' || $propertyName === 'persentase_refund') {
            $this->calculateEstimation();
        }
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        abort_if(Gate::denies('read-cancellation'), 403, 'Akses ditolak.');

        $data = PembatalanPesanan::with(['peminjaman.user', 'peminjaman.mobil'])
            ->when($this->search, function($q) {
                $q->whereHas('peminjaman.user', fn($sq) => $sq->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhereHas('peminjaman', fn($sq) => $sq->where('id', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterStatus, fn($q) => $q->where('refund_status', $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Ambil transaksi yang BISA dibatalkan (status belum batal/selesai/ditolak)
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
    // CRUD: CREATE (BATALKAN PESANAN)
    // =========================================================================

    public function create()
    {
        abort_if(Gate::denies('create-cancellation'), 403);
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-cancellation'), 403);

        $this->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'alasan' => 'required|string|min:5',
            'persentase_refund' => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::findOrFail($this->peminjaman_id);

            // 1. Cek Transaksi Pembayaran yang sukses (Settlement)
            $payments = PaymentTransaction::where('peminjaman_id', $peminjaman->id)
                ->where('status', 'settlement')
                ->get();

            // Jika tidak ada pembayaran masuk, batalkan saja tanpa refund
            if ($payments->isEmpty()) {
                $this->cancelWithoutRefund($peminjaman);
                DB::commit();
                $this->closeModal();
                $this->dispatch('notify', message: 'Pesanan dibatalkan (Tanpa Refund karena belum ada pembayaran).', type: 'warning');
                return;
            }

            // 2. Proses Refund (Midtrans / Manual)
            $totalRefund = 0;
            $refundTransactions = [];
            $decimalPercentage = $this->persentase_refund / 100;

            // Config Midtrans
            $serverKey = config('services.midtrans.server_key');
            $base64Auth = base64_encode($serverKey . ':');

            foreach ($payments as $payment) {
                $refundAmount = (int) round($decimalPercentage * $payment->amount);

                if ($refundAmount <= 0) continue;

                // Cek apakah pembayaran via Midtrans atau Manual
                if (str_contains($payment->midtrans_transaction_id, 'MANUAL')) {
                    // --- REFUND MANUAL ---
                    // Tidak perlu call API Midtrans, cukup catat di DB
                    $refundData = [
                        'status_code' => '200',
                        'transaction_status' => 'refund',
                        'gross_amount' => $refundAmount,
                        'order_id' => $payment->midtrans_transaction_id,
                        'refund_key' => 'MANUAL-REFUND-' . uniqid()
                    ];
                    
                } else {
                    // --- REFUND MIDTRANS API ---
                    $refundUrl = "https://api.sandbox.midtrans.com/v2/{$payment->midtrans_transaction_id}/refund";
                    
                    try {
                        $response = Http::withHeaders([
                            'Authorization' => 'Basic ' . $base64Auth,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ])->post($refundUrl, [
                            'refund_key' => 'refund_' . uniqid(),
                            'amount' => $refundAmount,
                            'reason' => $this->alasan,
                        ]);

                        $refundData = $response->json();
                    } catch (\Exception $e) {
                        // Jika gagal connect midtrans, lempar error agar rollback
                        throw new \Exception("Gagal koneksi ke Midtrans: " . $e->getMessage());
                    }
                }

                // Catat transaksi refund di database
                $statusRefund = ($refundData['status_code'] ?? '') == '200' ? 'refunded' : 'failed';
                
                if ($statusRefund == 'failed') {
                    throw new \Exception("Refund Gagal: " . ($refundData['status_message'] ?? 'Unknown error'));
                }

                $refundTrx = PaymentTransaction::create([
                    'peminjaman_id' => $peminjaman->id,
                    'midtrans_transaction_id' => $payment->midtrans_transaction_id,
                    'status' => 'refunded', // Kita asumsikan sukses jika code 200
                    'amount' => $refundAmount,
                    'tipe_transaksi' => 'refund',
                    'midtrans_response' => json_encode($refundData),
                    'id_transaksi_awal' => $payment->id,
                ]);

                $refundTransactions[] = $refundTrx;
                $totalRefund += $refundAmount;
            }

            // 3. Simpan Record Pembatalan
            PembatalanPesanan::create([
                'peminjaman_id' => $peminjaman->id,
                'cancelled_by' => 'admin', // atau Auth::user()->name
                'approval_status' => 'approved',
                'alasan' => $this->alasan,
                'refund_status' => ($totalRefund > 0) ? 'refunded' : 'pending_refund',
                'cancelled_at' => now(),
                'persentase_refund' => $decimalPercentage, // Simpan sebagai 0.x
                'jumlah_refund' => $totalRefund,
                'id_transaksi_refund' => $refundTransactions[0]->id ?? null,
            ]);

            // 4. Update Status Peminjaman & Mobil
            $peminjaman->update(['status' => 'dibatalkan', 'sudah_refund' => 1]);
            $peminjaman->mobil()->update(['status' => 'tersedia']);

            DB::commit();
            $this->closeModal();
            $this->dispatch('notify', message: 'Pesanan dibatalkan & Refund Rp '.number_format($totalRefund).' diproses.', type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Gagal: ' . $e->getMessage(), type: 'error');
        }
    }

    private function cancelWithoutRefund($peminjaman)
    {
        PembatalanPesanan::create([
            'peminjaman_id' => $peminjaman->id,
            'cancelled_by' => 'admin',
            'approval_status' => 'approved',
            'alasan' => $this->alasan,
            'refund_status' => 'no_refund_needed',
            'cancelled_at' => now(),
            'persentase_refund' => 0,
            'jumlah_refund' => 0,
        ]);

        $peminjaman->update(['status' => 'dibatalkan']);
        $peminjaman->mobil()->update(['status' => 'tersedia']);
    }

    // =========================================================================
    // CRUD: DETAIL
    // =========================================================================

    public function showDetail($id)
    {
        $this->selectedPembatalan = PembatalanPesanan::with(['peminjaman.user', 'peminjaman.mobil'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function calculateEstimation()
    {
        if ($this->peminjaman_id) {
            $peminjaman = Peminjaman::with('paymentTransactions')->find($this->peminjaman_id);
            if ($peminjaman) {
                // Hitung total yang sudah dibayar (settlement)
                $this->total_dibayarkan_customer = $peminjaman->paymentTransactions
                    ->where('status', 'settlement')
                    ->where('tipe_transaksi', '!=', 'refund') // Jangan hitung refund
                    ->sum('amount');

                // Hitung estimasi refund
                $persen = (float) $this->persentase_refund / 100;
                $this->estimasi_refund = $this->total_dibayarkan_customer * $persen;
            }
        }
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showDetailModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['peminjaman_id', 'alasan', 'persentase_refund', 'estimasi_refund', 'total_dibayarkan_customer', 'selectedPembatalan']);
        $this->resetErrorBag();
    }
}