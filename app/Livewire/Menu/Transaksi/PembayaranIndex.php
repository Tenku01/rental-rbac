<?php

namespace App\Livewire\Menu\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\TransaksiPembayaran; // 🔹 Diperbarui dari PaymentTransaction
use Illuminate\Support\Facades\Gate;

class PembayaranIndex extends Component
{
    use WithPagination;

    // --- Filters & Search ---
    public $search = '';
    public $filterStatus = 'all'; // 🔹 Diperbarui: Default 'all' untuk penanganan tab grup

    // --- Modal Detail ---
    public $showDetailModal = false;
    public $selectedPembayaran = null; // 🔹 Diperbarui dari selectedPayment
    public $rawJson = ''; // Untuk menampilkan respon mentah Midtrans

    protected $paginationTheme = 'tailwind';

    public function updatedSearch()
    {
        $this->resetPage();
    }
    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

   #[Layout('layouts.admin')]
    public function render()
    {
        // RBAC: Hanya user dengan izin 'read-transaksi_pembayaran' yang bisa melihat
        abort_if(Gate::denies('read-transaksi_pembayaran'), 403, 'Anda tidak memiliki akses melihat data keuangan.');

        $pembayaranList = TransaksiPembayaran::with(['peminjaman.user', 'peminjaman.mobil']) // 🔹 Diperbarui
            ->when($this->search, function ($q) {
                $q->where('id_transaksi_midtrans', 'like', '%' . $this->search . '%') // 🔹 Diperbarui
                    ->orWhere('id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('peminjaman.user', fn($sq) => $sq->where('name', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterStatus !== 'all', function ($q) {
                // 🔹 Logika Pengelompokan Tab Secara Cerdas
                if ($this->filterStatus === 'sukses') {
                    $q->whereIn('status', ['success', 'settlement', 'capture']);
                } elseif ($this->filterStatus === 'pending') {
                    $q->where('status', 'pending');
                } elseif ($this->filterStatus === 'gagal') {
                    $q->whereIn('status', ['deny', 'cancel', 'expire', 'failed']);
                } elseif ($this->filterStatus === 'refund') {
                    $q->where('tipe_transaksi', 'refund')->orWhere('status', 'refunded');
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)->withPath(url()->current());

        return view('livewire.menu.transaksi.pembayaran-index', [
            'pembayaranList' => $pembayaranList // 🔹 Diperbarui
        ]);
    }

    // --- Action: Show Detail ---
    public function showDetail($id)
    {
        // 🔹 Diperbarui: Membutuhkan hak akses 'read-transaksi_pembayaran' yang valid di Spatie
        abort_if(Gate::denies('read-transaksi_pembayaran'), 403);

        $this->selectedPembayaran = TransaksiPembayaran::with(['peminjaman.user', 'peminjaman.mobil'])->findOrFail($id); // 🔹 Diperbarui

        // Format JSON agar mudah dibaca di modal
        // 🔹 Diperbarui: midtrans_response -> respon_midtrans
        $this->rawJson = json_encode(json_decode($this->selectedPembayaran->respon_midtrans), JSON_PRETTY_PRINT);

        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedPembayaran = null; // 🔹 Diperbarui
        $this->rawJson = '';
    }
}
