<?php

namespace App\Livewire\Menu\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\DriverLogbook;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DriverLogbookIndex extends Component
{
    use WithPagination;
    use WithFileUploads;

    // --- Filters & Search ---
    public $search = '';

    // --- Modal States ---
    public $showModal = false;
    public $showDetailModal = false;
    public $isEditMode = false;
    public $selectedLog = null;
    public $modalTitle = ''; // Ditambahkan properti modalTitle

    // --- Form Fields ---
    public $editingId = null;
    public $peminjaman_id;
    public $tanggal_aktivitas;
    public $deskripsi_aktivitas;
    public $status_log = 'mulai_kerja';
    public $foto_bukti;

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }

    #[Layout('layouts.admin')]
    public function render()
    {
        // RBAC: Read Permission
        abort_if(Gate::denies('read-driver_logbooks'), 403, 'Akses ditolak.');

        $logs = DriverLogbook::with(['peminjaman.sopir', 'peminjaman.user', 'peminjaman.mobil'])
            ->when($this->search, function($q) {
                $q->where('id', 'like', '%'.$this->search.'%')
                  ->orWhereHas('peminjaman.sopir', fn($sq) => $sq->where('nama', 'like', '%'.$this->search.'%'))
                  ->orWhereHas('peminjaman.mobil', fn($sq) => $sq->where('plat_nomor', 'like', '%'.$this->search.'%'));
            })
            ->orderBy('waktu_log', 'desc')
            ->paginate(10);

        // Ambil peminjaman aktif yang menggunakan sopir untuk pilihan di modal
        $activeRentals = Peminjaman::with(['sopir', 'user', 'mobil'])
            ->where('add_on_sopir', 1)
            ->whereIn('status', ['berlangsung', 'sudah dibayar lunas'])
            ->get();

        return view('livewire.menu.operasional.driver-logbook-index', [
            'logs' => $logs,
            'active_rentals' => $activeRentals
        ]);
    }

    // =========================================================================
    // CRUD: CREATE, UPDATE, DELETE
    // =========================================================================

    public function create()
    {
        abort_if(Gate::denies('create-driver_logbooks'), 403);
        $this->resetForm();
        $this->tanggal_aktivitas = now()->toDateString();
        $this->modalTitle = 'Catat Aktivitas Sopir';
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-driver_logbooks'), 403);

        $this->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'tanggal_aktivitas' => 'required|date',
            'deskripsi_aktivitas' => 'required|string|min:5',
            'status_log' => 'required|in:mulai_kerja,dalam_perjalanan,selesai_hari_ini,selesai_peminjaman',
            'foto_bukti' => 'nullable|image|max:2048'
        ]);

        $path = $this->foto_bukti ? $this->foto_bukti->store('logbooks', 'public') : null;

        DriverLogbook::create([
            'peminjaman_id' => $this->peminjaman_id,
            'tanggal_aktivitas' => $this->tanggal_aktivitas,
            'deskripsi_aktivitas' => $this->deskripsi_aktivitas,
            'status_log' => $this->status_log,
            'foto_bukti' => $path,
            'waktu_log' => now()
        ]);

        $this->closeModal();
        $this->dispatch('notify', message: 'Aktivitas sopir berhasil dicatat.', type: 'success');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('update-driver_logbooks'), 403);
        $log = DriverLogbook::findOrFail($id);

        $this->editingId = $id;
        $this->peminjaman_id = $log->peminjaman_id;
        $this->tanggal_aktivitas = $log->tanggal_aktivitas;
        $this->deskripsi_aktivitas = $log->deskripsi_aktivitas;
        $this->status_log = $log->status_log;
        
        $this->modalTitle = 'Koreksi Log Aktivitas';
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-driver_logbooks'), 403);

        $log = DriverLogbook::findOrFail($this->editingId);

        $this->validate([
            'tanggal_aktivitas' => 'required|date',
            'deskripsi_aktivitas' => 'required|string',
            'status_log' => 'required',
            'foto_bukti' => 'nullable|image|max:2048'
        ]);

        $data = [
            'tanggal_aktivitas' => $this->tanggal_aktivitas,
            'deskripsi_aktivitas' => $this->deskripsi_aktivitas,
            'status_log' => $this->status_log,
        ];

        if ($this->foto_bukti) {
            if ($log->foto_bukti) Storage::disk('public')->delete($log->foto_bukti);
            $data['foto_bukti'] = $this->foto_bukti->store('logbooks', 'public');
        }

        $log->update($data);

        $this->closeModal();
        $this->dispatch('notify', message: 'Logbook diperbarui.', type: 'success');
    }

    public function delete($id)
    {
        abort_if(Gate::denies('delete-driver_logbooks'), 403);
        $log = DriverLogbook::findOrFail($id);
        if ($log->foto_bukti) Storage::disk('public')->delete($log->foto_bukti);
        $log->delete();

        $this->dispatch('notify', message: 'Log aktivitas dihapus.', type: 'warning');
    }

    public function showDetail($id)
    {
        $this->selectedLog = DriverLogbook::with(['peminjaman.sopir', 'peminjaman.user', 'peminjaman.mobil'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showDetailModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['editingId', 'peminjaman_id', 'tanggal_aktivitas', 'deskripsi_aktivitas', 'status_log', 'foto_bukti', 'isEditMode', 'selectedLog', 'modalTitle']);
        $this->resetErrorBag();
    }
}