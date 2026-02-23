<?php

namespace App\Livewire\Menu\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\VehicleDamageReport;
use App\Models\Mobil;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VehicleDamageIndex extends Component
{
    use WithPagination;

    // --- Filters & Search ---
    public $search = '';

    // --- Modal States ---
    public $showEditModal = false;
    public $showDetailModal = false;
    public $selectedReport = null;

    // --- Form Fields (Update) ---
    public $editingKode = null; // Menggunakan kode_laporan sebagai ID
    public $damage_description;
    public $damage_cost = 0;

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }

    #[Layout('layouts.admin')]
    public function render()
    {
        // RBAC: Read Permission
        abort_if(Gate::denies('read-damage'), 403, 'Akses ditolak.');

        $reports = VehicleDamageReport::with(['mobil', 'pengembalian'])
            ->when($this->search, function($q) {
                $q->where('kode_laporan', 'like', '%'.$this->search.'%')
                  ->orWhere('mobil_id', 'like', '%'.$this->search.'%')
                  ->orWhere('pengembalian_kode', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.menu.operasional.vehicle-damage-index', [
            'reports' => $reports
        ]);
    }

    // =========================================================================
    // ACTION & CRUD
    // =========================================================================

    public function showDetail($kode)
    {
        $this->selectedReport = VehicleDamageReport::with(['mobil', 'pengembalian.peminjaman.user'])->where('kode_laporan', $kode)->firstOrFail();
        $this->showDetailModal = true;
    }

    public function edit($kode)
    {
        abort_if(Gate::denies('update-damage'), 403);
        $report = VehicleDamageReport::where('kode_laporan', $kode)->firstOrFail();
        
        $this->editingKode = $kode;
        $this->damage_description = $report->damage_description;
        $this->damage_cost = $report->damage_cost;
        
        $this->showEditModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-damage'), 403);

        $this->validate([
            'damage_description' => 'required|string',
            'damage_cost' => 'required|numeric|min:0',
        ]);

        try {
            $report = VehicleDamageReport::where('kode_laporan', $this->editingKode)->firstOrFail();
            
            $report->update([
                'damage_description' => $this->damage_description,
                'damage_cost' => $this->damage_cost,
            ]);

            $this->closeModal();
            $this->dispatch('notify', message: 'Laporan #' . $this->editingKode . ' berhasil diperbarui.', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Gagal: ' . $e->getMessage(), type: 'error');
        }
    }

    public function delete($kode)
    {
        abort_if(Gate::denies('delete-damage'), 403);
        VehicleDamageReport::where('kode_laporan', $kode)->delete();
        $this->dispatch('notify', message: 'Laporan kerusakan dihapus.', type: 'warning');
    }

    public function closeModal()
    {
        $this->showEditModal = false;
        $this->showDetailModal = false;
        $this->reset(['editingKode', 'damage_description', 'damage_cost', 'selectedReport']);
    }
}