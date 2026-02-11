<?php

namespace App\Livewire\Menu\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\VehicleDamageReport;

class VehicleDamageIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedReport = null;
    public $showDetailModal = false;

    protected $paginationTheme = 'tailwind';

    #[Layout('layouts.admin')]
    public function render()
    {
        $reports = VehicleDamageReport::with(['mobil', 'pengembalian'])
            ->when($this->search, function($q) {
                $q->where('kode_laporan', 'like', '%'.$this->search.'%')
                  ->orWhere('mobil_id', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.vehicle-damage-index', [
            'reports' => $reports
        ]);
    }

    public function showDetail($id)
    {
        $this->selectedReport = VehicleDamageReport::with(['mobil', 'pengembalian'])->find($id);
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedReport = null;
    }
}