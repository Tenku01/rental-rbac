<?php

namespace App\Livewire\Menu\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Pengembalian; // Menggunakan Pengembalian karena denda sudah di-merge
use Illuminate\Support\Facades\Gate;

class DendaIndex extends Component
{
    use WithPagination;

    // --- Filters & Search ---
    public $search = '';
    public $filterStatus = ''; // belum dibayar, sudah dibayar

    // --- Modal Detail & Payment ---
    public $showDetailModal = false;
    public $showPaymentModal = false;
    public $selectedDenda = null; // Menyimpan instance Pengembalian

    // --- Form Payment (Update Status) ---
    public $metode_pembayaran = 'cash';
    public $keterangan_bayar;

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
        abort_if(Gate::denies('read-denda'), 403, 'Akses ditolak.');

        // Hanya mengambil data pengembalian yang memiliki denda
        $dendaList = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
            ->where('status_denda', '!=', 'tidak ada denda')
            ->when($this->search, function ($q) {
                $q->where('kode_pengembalian', 'like', '%' . $this->search . '%')
                    ->orWhereHas('peminjaman.user', fn($sq) => $sq->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('peminjaman.mobil', fn($sq) => $sq->where('id', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterStatus, fn($q) => $q->where('status_denda', $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate(10)->withPath(url()->current());

        return view('livewire.menu.transaksi.denda-index', [
            'dendaList' => $dendaList
        ]);
    }

    // =========================================================================
    // ACTION: DETAIL & PAYMENT
    // =========================================================================

    public function showDetail($kode_pengembalian)
    {
        $this->selectedDenda = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
            ->where('kode_pengembalian', $kode_pengembalian)
            ->firstOrFail();
        $this->showDetailModal = true;
    }

    public function openPaymentModal($kode_pengembalian)
    {
        abort_if(Gate::denies('update-denda'), 403);

        $this->selectedDenda = Pengembalian::with(['peminjaman.user'])
            ->where('kode_pengembalian', $kode_pengembalian)
            ->firstOrFail();
            
        $this->metode_pembayaran = 'cash';
        $this->keterangan_bayar = '';
        $this->showPaymentModal = true;
    }

    public function processPayment()
    {
        abort_if(Gate::denies('update-denda'), 403);

        $this->validate([
            'metode_pembayaran' => 'required|in:cash,transfer',
            'keterangan_bayar' => 'nullable|string|max:255'
        ]);

        $keteranganLama = $this->selectedDenda->keterangan_denda ? $this->selectedDenda->keterangan_denda . ' ' : '';

        $this->selectedDenda->update([
            'status_denda' => 'sudah dibayar',
            'tanggal_pembayaran_denda' => now(), 
            'metode_pembayaran_denda' => $this->metode_pembayaran, 
            'keterangan_denda' => $keteranganLama . '(LUNAS: ' . ($this->keterangan_bayar ?? '-') . ')'
        ]);

        $this->closeModal();
        $this->dispatch('notify', message: 'Status denda berhasil diperbarui menjadi LUNAS.', type: 'success');
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->showPaymentModal = false;
        $this->selectedDenda = null;
        $this->reset(['metode_pembayaran', 'keterangan_bayar']);
    }
}