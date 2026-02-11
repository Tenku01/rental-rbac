<?php

namespace App\Livewire\Menu\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Peminjaman;

class PeminjamanIndex extends Component
{
    use WithPagination;

    // --- Filters & Search ---
    public $filterStatus = '';
    public $search = '';

    // --- Modal States ---
    public $selectedPeminjaman = null;
    public $showDetailModal = false;
    
    // Properti untuk aksi (Cancel/Verifikasi) DIHAPUS karena mode Read-Only

    protected $paginationTheme = 'tailwind';

    #[Layout('layouts.admin')]
    public function render()
    {
        $query = Peminjaman::with(['user', 'mobil', 'sopir'])
            ->orderBy('created_at', 'desc');

        // Filter Status Dropdown
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Search Logic (User / Mobil)
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('user', function($subQ) {
                    $subQ->where('name', 'like', '%'.$this->search.'%');
                })->orWhereHas('mobil', function($subQ) {
                    $subQ->where('merek', 'like', '%'.$this->search.'%')
                         ->orWhere('tipe', 'like', '%'.$this->search.'%');
                });
            });
        }

        return view('livewire.admin.peminjaman-index', [
            'peminjaman' => $query->paginate(10)
        ]);
    }

    // --- Menampilkan Detail Transaksi (Hanya Read) ---
    public function showDetail($id)
    {
        // Eager load relasi pembayaran dan denda untuk dilihat admin
        $this->selectedPeminjaman = Peminjaman::with(['paymentTransactions', 'fines'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    // Reset modal saat ditutup
    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedPeminjaman = null;
    }
}