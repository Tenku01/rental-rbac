<?php

namespace App\Livewire\Menu\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Denda; // 🔹 Diperbarui dari Fine
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
    public $selectedDenda = null; // 🔹 Diperbarui dari selectedFine

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
        // RBAC: Izin melihat denda (Diselaraskan dengan web.php: read-denda)
        abort_if(Gate::denies('read-denda'), 403, 'Akses ditolak.');

        $dendaList = Denda::with(['peminjaman.user', 'peminjaman.mobil']) // 🔹 Diperbarui
            ->when($this->search, function ($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('peminjaman.user', fn($sq) => $sq->where('name', 'like', '%' . $this->search . '%'))
                    // 🔹 Diperbarui: plat_nomor di database baru menggunakan kolom id di tabel mobils
                    ->orWhereHas('peminjaman.mobil', fn($sq) => $sq->where('id', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate(10)->withPath(url()->current());

        // 🔹 Diperbarui nama view-nya menjadi denda-index
        return view('livewire.menu.transaksi.denda-index', [
            'dendaList' => $dendaList
        ]);
    }

    // =========================================================================
    // ACTION: DETAIL & PAYMENT
    // =========================================================================

    public function showDetail($id)
    {
        $this->selectedDenda = Denda::with(['peminjaman.user', 'peminjaman.mobil'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function openPaymentModal($id)
    {
        // RBAC: Hanya boleh update jika punya izin
        abort_if(Gate::denies('update-denda'), 403);

        $this->selectedDenda = Denda::with(['peminjaman.user'])->findOrFail($id);
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

        $this->selectedDenda->update([
            'status' => 'sudah dibayar',
            'tanggal_pembayaran' => now(), // Pastikan migration Anda memiliki kolom ini
            'metode_pembayaran' => $this->metode_pembayaran, // Pastikan migration Anda memiliki kolom ini
            'keterangan' => $this->selectedDenda->keterangan . ' (LUNAS: ' . ($this->keterangan_bayar ?? '-') . ')'
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
