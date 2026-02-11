<?php

namespace App\Livewire\Menu\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\DriverLogbook;

class DriverLogbookIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedLog = null;
    public $showDetailModal = false;

    protected $paginationTheme = 'tailwind';

    #[Layout('layouts.admin')]
    public function render()
    {
        $logs = DriverLogbook::with(['peminjaman.sopir']) // Relasi sopir lewat peminjaman
            ->when($this->search, function($q) {
                $q->whereHas('peminjaman.sopir', function($sub) {
                    $sub->where('nama', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('waktu_log', 'desc')
            ->paginate(10);

        return view('livewire.admin.driver-logbook-index', [
            'logs' => $logs
        ]);
    }

    public function showDetail($id)
    {
        $this->selectedLog = DriverLogbook::with('peminjaman.sopir')->find($id);
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedLog = null;
    }
}