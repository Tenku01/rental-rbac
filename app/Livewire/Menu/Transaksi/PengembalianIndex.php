<?php

namespace App\Livewire\Menu\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Pengembalian;
use App\Models\Peminjaman;
use App\Models\Mobil;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PengembalianIndex extends Component
{
    use WithPagination;
    use WithFileUploads;

    // --- Filters ---
    public $search = '';
    public $filterStatus = '';

    // --- Modal States ---
    public $showCreateModal = false;
    public $showDetailModal = false;
    public $isEditMode = false;
    public $selectedPengembalian = null;

    // --- Form Fields ---
    public $editingId = null;
    public $peminjaman_id;
    public $tanggal_pengembalian;
    public $kondisi_mobil_kembali;
    public $denda = 0;
    public $catatan_pengembalian;
    public $bukti_foto; // Opsional

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    #[Layout('layouts.admin')]
    public function render()
    {
        abort_if(Gate::denies('read-pengembalian'), 403);

        $query = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
            ->when($this->search, function($q) {
                $q->where('id', 'like', '%'.$this->search.'%')
                  ->orWhereHas('peminjaman.user', fn($sq) => $sq->where('name', 'like', '%'.$this->search.'%'))
                  // 🔹 Diperbarui: plat_nomor diubah menjadi id sesuai dengan relasi tabel mobils yang baru
                  ->orWhereHas('peminjaman.mobil', fn($sq) => $sq->where('id', 'like', '%'.$this->search.'%'));
            })
            // 🔹 Diperbarui: status_kondisi diubah menjadi status agar sinkron dengan field di database
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('tanggal_pengembalian', 'desc');

        // Ambil daftar peminjaman yang SEDANG BERJALAN untuk form Create
        $activeRentals = Peminjaman::with(['user', 'mobil'])
            ->where('status', 'berlangsung')
            ->get();

        return view('livewire.menu.transaksi.pengembalian-index', [
            'pengembalian' => $query->paginate(10),
            'active_rentals' => $activeRentals
        ]);
    }

    // =========================================================================
    // CRUD: CREATE
    // =========================================================================

    public function create()
    {
        abort_if(Gate::denies('create-pengembalian'), 403);
        $this->resetForm();
        $this->tanggal_pengembalian = now()->toDateString();
        $this->showCreateModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-pengembalian'), 403);

        $this->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'tanggal_pengembalian' => 'required|date',
            'kondisi_mobil_kembali' => 'required|string',
            'denda' => 'required|numeric|min:0',
            'bukti_foto' => 'nullable|image|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::findOrFail($this->peminjaman_id);
            
            // 1. Simpan Data Pengembalian
            $path = $this->bukti_foto ? $this->bukti_foto->store('pengembalian', 'public') : null;

            Pengembalian::create([
                'peminjaman_id' => $this->peminjaman_id,
                'tanggal_pengembalian' => $this->tanggal_pengembalian,
                'kondisi_mobil' => $this->kondisi_mobil_kembali,
                'denda' => $this->denda,
                'catatan' => $this->catatan_pengembalian,
                'foto_kondisi' => $path,
                'admin_id' => Auth::id()
            ]);

            // 2. Update Status Peminjaman jadi SELESAI
            $peminjaman->update(['status' => 'selesai']);

            // 3. Update Status Mobil jadi TERSEDIA (atau Dibersihkan)
            $peminjaman->mobil()->update(['status' => 'tersedia']);

            DB::commit();
            $this->closeModal();
            $this->dispatch('notify', message: 'Data pengembalian berhasil diproses.', type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }

    // =========================================================================
    // CRUD: UPDATE & DETAIL
    // =========================================================================

    public function showDetail($id)
    {
        $this->selectedPengembalian = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function edit($id)
    {
        abort_if(Gate::denies('update-pengembalian'), 403);
        $p = Pengembalian::findOrFail($id);
        
        $this->editingId = $id;
        $this->peminjaman_id = $p->peminjaman_id;
        $this->tanggal_pengembalian = $p->tanggal_pengembalian;
        $this->kondisi_mobil_kembali = $p->kondisi_mobil;
        $this->denda = $p->denda;
        $this->catatan_pengembalian = $p->catatan;
        
        $this->isEditMode = true;
        $this->showCreateModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-pengembalian'), 403);

        $p = Pengembalian::findOrFail($this->editingId);
        $p->update([
            'tanggal_pengembalian' => $this->tanggal_pengembalian,
            'kondisi_mobil' => $this->kondisi_mobil_kembali,
            'denda' => $this->denda,
            'catatan' => $this->catatan_pengembalian,
        ]);

        $this->closeModal();
        $this->dispatch('notify', message: 'Data pengembalian diperbarui.', type: 'success');
    }

    public function delete($id)
    {
        abort_if(Gate::denies('delete-pengembalian'), 403);
        
        DB::beginTransaction();
        try {
            $p = Pengembalian::findOrFail($id);
            
            // Rollback status mobil & peminjaman jika data dihapus (opsional, tergantung kebijakan)
            $p->peminjaman()->update(['status' => 'berlangsung']);
            $p->peminjaman->mobil()->update(['status' => 'disewa']);
            
            $p->delete();
            DB::commit();
            $this->dispatch('notify', message: 'Data pengembalian dihapus, status mobil dikembalikan ke Disewa.', type: 'warning');
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showDetailModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'peminjaman_id', 'tanggal_pengembalian', 'kondisi_mobil_kembali', 
            'denda', 'catatan_pengembalian', 'bukti_foto', 'editingId', 'isEditMode'
        ]);
        $this->resetErrorBag();
    }
}