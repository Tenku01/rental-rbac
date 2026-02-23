<?php

namespace App\Livewire\Menu\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Fine; // Gunakan model Fine
use Illuminate\Support\Facades\Gate;

class FineIndex extends Component
{
    use WithPagination;

    // --- Filters & Search ---
    public $search = '';
    public $filterStatus = ''; // belum dibayar, sudah dibayar

    // --- Modal Detail & Payment ---
    public $showDetailModal = false;
    public $showPaymentModal = false;
    public $selectedFine = null;

    // --- Form Payment (Update Status) ---
    public $metode_pembayaran = 'cash';
    public $keterangan_bayar;

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    #[Layout('layouts.admin')]
    public function render()
    {
        // RBAC: Izin melihat denda
        abort_if(Gate::denies('read-fine'), 403, 'Akses ditolak.');

        $fines = Fine::with(['peminjaman.user', 'peminjaman.mobil'])
            ->when($this->search, function($q) {
                $q->where('id', 'like', '%'.$this->search.'%')
                  ->orWhereHas('peminjaman.user', fn($sq) => $sq->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhereHas('peminjaman.mobil', fn($sq) => $sq->where('plat_nomor', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.menu.transaksi.fine-index', [
            'fines' => $fines
        ]);
    }

    // =========================================================================
    // ACTION: DETAIL & PAYMENT
    // =========================================================================

    public function showDetail($id)
    {
        $this->selectedFine = Fine::with(['peminjaman.user', 'peminjaman.mobil'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function openPaymentModal($id)
    {
        // RBAC: Hanya boleh update jika punya izin
        abort_if(Gate::denies('update-fine'), 403);

        $this->selectedFine = Fine::with(['peminjaman.user'])->findOrFail($id);
        $this->metode_pembayaran = 'cash';
        $this->keterangan_bayar = '';
        $this->showPaymentModal = true;
    }

    public function processPayment()
    {
        abort_if(Gate::denies('update-fine'), 403);

        $this->validate([
            'metode_pembayaran' => 'required|in:cash,transfer',
            'keterangan_bayar' => 'nullable|string|max:255'
        ]);

        $this->selectedFine->update([
            'status' => 'sudah dibayar',
            'tanggal_pembayaran' => now(),
            'metode_pembayaran' => $this->metode_pembayaran,
            'keterangan' => $this->selectedFine->keterangan . ' (LUNAS: ' . ($this->keterangan_bayar ?? '-') . ')'
        ]);

        $this->closeModal();
        $this->dispatch('notify', message: 'Status denda berhasil diperbarui menjadi LUNAS.', type: 'success');
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->showPaymentModal = false;
        $this->selectedFine = null;
        $this->reset(['metode_pembayaran', 'keterangan_bayar']);
    }
}