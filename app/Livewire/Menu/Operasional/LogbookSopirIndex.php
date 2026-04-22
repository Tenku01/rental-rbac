<?php

namespace App\Livewire\Menu\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\LogbookSopir;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class LogbookSopirIndex extends Component
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
    public $modalTitle = '';

    // --- Form Fields ---
    public $editingId = null;
    public $peminjaman_id;
    public $tanggal_aktivitas;
    public $deskripsi_aktivitas;
    public $status_log = 'mulai_kerja';
    public $foto_bukti;

    protected $paginationTheme = 'tailwind';

    // Hook untuk mengecek hak akses saat komponen pertama kali dimuat
    public function mount()
    {
        // 🔹 Permission name tetap dipertahankan karena tabel Spatie tidak di-translate
        if (Gate::denies('read-driver_logbooks')) {
            return redirect()->route('unauthorized');
        }
    }

    public function updatedSearch() { $this->resetPage(); }

    // Hook untuk validasi real-time saat user mengetik
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    protected function rules()
    {
        $rules = [
            'tanggal_aktivitas' => 'required|date',
            'deskripsi_aktivitas' => 'required|string|min:5',
            'status_log' => 'required|in:mulai_kerja,dalam_perjalanan,selesai_hari_ini,selesai_peminjaman',
            'foto_bukti' => 'nullable|image|max:2048'
        ];

        // Jika mode tambah baru, peminjaman_id wajib diisi
        if (!$this->isEditMode) {
            $rules['peminjaman_id'] = 'required|exists:peminjaman,id';
        }

        return $rules;
    }

    // Pesan error Custom Bahasa Indonesia
    protected function messages()
    {
        return [
            'peminjaman_id.required' => 'Penugasan armada dan sopir wajib dipilih.',
            'peminjaman_id.exists' => 'Data penugasan tidak valid di sistem.',
            'tanggal_aktivitas.required' => 'Tanggal aktivitas wajib ditentukan.',
            'tanggal_aktivitas.date' => 'Format tanggal tidak valid.',
            'deskripsi_aktivitas.required' => 'Deskripsi kegiatan / perjalanan wajib diisi.',
            'deskripsi_aktivitas.min' => 'Deskripsi terlalu singkat (minimal 5 karakter).',
            'status_log.required' => 'Status perjalanan saat ini wajib dipilih.',
            'status_log.in' => 'Status perjalanan tidak valid.',
            'foto_bukti.image' => 'File bukti harus berupa gambar (JPG, PNG).',
            'foto_bukti.max' => 'Ukuran file gambar maksimal 2MB.',
        ];
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $logs = LogbookSopir::with(['peminjaman.sopir', 'peminjaman.user', 'peminjaman.mobil'])
            ->when($this->search, function($q) {
                $q->where('id', 'like', '%'.$this->search.'%')
                  ->orWhereHas('peminjaman.sopir', fn($sq) => $sq->where('nama', 'like', '%'.$this->search.'%'))
                  ->orWhereHas('peminjaman.mobil', fn($sq) => $sq->where('id', 'like', '%'.$this->search.'%'));
            })
            ->orderBy('waktu_log', 'desc')
            ->paginate(10);

        // Ambil peminjaman aktif yang menggunakan sopir untuk pilihan di modal
        $activeRentals = Peminjaman::with(['sopir', 'user', 'mobil'])
            ->where('tambahan_sopir', 1) 
            ->whereIn('status', ['berlangsung', 'sudah dibayar lunas'])
            ->get();

        // 🔹 NAMA VIEW SUDAH DISESUAIKAN
        return view('livewire.menu.operasional.logbook-sopir-index', [
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
        
        $this->validate();

        $path = $this->foto_bukti ? $this->foto_bukti->store('logbooks', 'public') : null;

        LogbookSopir::create([
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
        
        $log = LogbookSopir::findOrFail($id);

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

        $log = LogbookSopir::findOrFail($this->editingId);

        $this->validate();

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
        
        $log = LogbookSopir::findOrFail($id);
        if ($log->foto_bukti) Storage::disk('public')->delete($log->foto_bukti);
        $log->delete();

        $this->dispatch('notify', message: 'Log aktivitas dihapus.', type: 'warning');
    }

    public function showDetail($id)
    {
        $this->selectedLog = LogbookSopir::with(['peminjaman.sopir', 'peminjaman.user', 'peminjaman.mobil'])->findOrFail($id);
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