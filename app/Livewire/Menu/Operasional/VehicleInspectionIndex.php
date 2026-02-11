<?php

namespace App\Livewire\Menu\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\VehicleInspection;

class VehicleInspectionIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCondition = '';
    public $selectedInspection = null;
    public $showDetailModal = false;

    protected $paginationTheme = 'tailwind';

    #[Layout('layouts.admin')]
    public function render()
    {
        $inspections = VehicleInspection::with(['mobil', 'staff', 'pengembalian'])
            ->when($this->search, function($q) {
                $q->where('mobil_id', 'like', '%'.$this->search.'%');
            })
            ->when($this->filterCondition, function($q) {
                $q->where('condition', $this->filterCondition);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.vehicle-inspection-index', [
            'inspections' => $inspections
        ]);
    }

    public function showDetail($id)
    {
        $this->selectedInspection = VehicleInspection::with(['mobil', 'staff'])->find($id);
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedInspection = null;
    }
}