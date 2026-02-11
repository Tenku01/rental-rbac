<?php

namespace App\Livewire\Menu\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Pengembalian;

class PengembalianIndex extends Component
{
    use WithPagination;

    // Filter & Search
    public $search = '';
    public $filterStatus = '';

    // Modal Detail
    public $selectedPengembalian = null;
    public $showDetailModal = false;

    protected $paginationTheme = 'tailwind';

    #[Layout('layouts.admin')]
    public function render()
    {
        // Query dengan Eager Loading
        $query = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil',])
            ->orderBy('tanggal_pengembalian', 'desc');

        // Filter Status
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Search Logic
        if ($this->search) {
            $query->where(function($q) {
                $q->where('kode_pengembalian', 'like', '%'.$this->search.'%')
                  ->orWhereHas('peminjaman.user', function($subQ) {
                      $subQ->where('name', 'like', '%'.$this->search.'%');
                  })
                  ->orWhereHas('peminjaman.mobil', function($subQ) {
                      $subQ->where('id', 'like', '%'.$this->search.'%'); // Cari Plat Nomor
                  });
            });
        }

        return view('livewire.admin.pengembalian-index', [
            'pengembalian' => $query->paginate(10)
        ]);
    }

    // --- Action: Show Detail ---
    public function showDetail($kode)
    {
        $this->selectedPengembalian = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
            ->where('kode_pengembalian', $kode)
            ->firstOrFail();

        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedPengembalian = null;
    }
}