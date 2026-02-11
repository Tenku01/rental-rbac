<?php

namespace App\Livewire\Menu\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\PembatalanPesanan;

class PembatalanPesananIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = '';
    public $selectedBatal = null;
    public $showDetailModal = false;

    protected $paginationTheme = 'tailwind';

    #[Layout('layouts.admin')]
    public function render()
    {
        $data = PembatalanPesanan::with(['peminjaman.user', 'user'])
            ->when($this->search, function($q) {
                $q->whereHas('user', function($sub) {
                    $sub->where('name', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterStatus, function($q) {
                $q->where('approval_status', $this->filterStatus);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.pembatalan-pesanan-index', [
            'pembatalan' => $data
        ]);
    }

    public function showDetail($id)
    {
        $this->selectedBatal = PembatalanPesanan::with(['peminjaman.mobil', 'user'])->find($id);
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedBatal = null;
    }
}