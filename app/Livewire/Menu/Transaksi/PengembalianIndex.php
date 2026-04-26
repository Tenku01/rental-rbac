<?php

namespace App\Livewire\Menu\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Pengembalian;
use App\Models\Peminjaman;
use App\Models\Denda;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
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
    
    // --- Data Properties ---
    public $selectedPengembalian = null;
    public $selectedDenda = null; 

    // --- Form Fields ---
    public $editingKode = null; // 🔹 Diubah dari editingId ke editingKode
    public $peminjaman_id;
    public $tanggal_pengembalian;
    public $kondisi_mobil_kembali;
    public $catatan_pengembalian;
    public $bukti_foto; 

    protected $paginationTheme = 'tailwind';

    // =========================================================================
    // VALIDASI REALTIME & RULES
    // =========================================================================
    
    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    protected function rules()
    {
        return [
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'tanggal_pengembalian' => 'required|date|before_or_equal:today',
            'kondisi_mobil_kembali' => [
                'required', 
                'string', 
                'min:5', 
                'max:1000',
                'regex:/[a-zA-Z]{3,}/', 
                'not_regex:/[@#\^{}|<>\~]/' 
            ],
            'catatan_pengembalian' => [
                'nullable', 
                'string', 
                'max:500',
                'regex:/[a-zA-Z]{3,}/', 
                'not_regex:/[@#\^{}|<>\~]/'
            ],
        ];
    }

    protected $messages = [
        'peminjaman_id.required' => 'Silakan pilih data transaksi peminjaman.',
        'peminjaman_id.exists' => 'Data peminjaman tidak valid.',
        'tanggal_pengembalian.required' => 'Tanggal pengembalian wajib diisi.',
        'tanggal_pengembalian.before_or_equal' => 'Tanggal pengembalian tidak masuk akal (melebihi hari ini).',
        
        'kondisi_mobil_kembali.required' => 'Kondisi mobil wajib dijelaskan secara detail.',
        'kondisi_mobil_kembali.min' => 'Deskripsi terlalu singkat (minimal 5 karakter).',
        'kondisi_mobil_kembali.max' => 'Deskripsi maksimal 1000 karakter.',
        'kondisi_mobil_kembali.regex' => 'Harus mengandung kalimat bermakna, tidak boleh hanya angka atau simbol.',
        'kondisi_mobil_kembali.not_regex' => 'Mengandung simbol yang tidak valid (@, #, <, >, dll).',
        
        'catatan_pengembalian.max' => 'Catatan maksimal 500 karakter.',
        'catatan_pengembalian.regex' => 'Harus mengandung kalimat bermakna, tidak boleh hanya angka atau simbol.',
        'catatan_pengembalian.not_regex' => 'Mengandung simbol yang tidak valid.',
    ];

    #[Layout('layouts.admin')]
    public function render()
    {
        abort_if(Gate::denies('read-pengembalian'), 403);

        $query = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
            ->when($this->search, function($q) {
                $q->where('kode_pengembalian', 'like', '%'.$this->search.'%')
                  ->orWhereHas('peminjaman.user', fn($sq) => $sq->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhereHas('peminjaman.mobil', fn($sq) => $sq->where('id', 'like', '%'.$this->search.'%'));
            })
            ->orderBy('tanggal_pengembalian', 'desc');

        $pengembalianData = $query->paginate(10)->withPath(url()->current());

        // LOGIKA CEK DENDA
        $peminjamanIds = $pengembalianData->pluck('peminjaman_id')->toArray();
        $dendaList = Denda::whereIn('peminjaman_id', $peminjamanIds)
            ->get()
            ->keyBy('peminjaman_id');

        $activeRentals = Peminjaman::with(['user', 'mobil'])
            ->where('status', 'berlangsung')
            ->get();

        return view('livewire.menu.transaksi.pengembalian-index', [
            'pengembalian' => $pengembalianData,
            'dendaList' => $dendaList, 
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

        $this->validate();

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::findOrFail($this->peminjaman_id);
            
            $platNomor = str_replace(' ', '', $peminjaman->mobil_id);
            $kodePengembalian = 'RET-' . strtoupper($platNomor) . '-' . $peminjaman->id;

            Pengembalian::create([
                'kode_pengembalian' => $kodePengembalian,
                'peminjaman_id' => $this->peminjaman_id,
                'tanggal_pengembalian' => $this->tanggal_pengembalian,
                'kondisi_mobil' => trim($this->kondisi_mobil_kembali),
                'denda' => 0, 
                'catatan' => trim($this->catatan_pengembalian),
                'admin_id' => Auth::id()
            ]);

            $peminjaman->update(['status' => 'selesai']);
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

    // 🔹 Diperbarui: Pencarian menggunakan parameter $kode_pengembalian
    public function showDetail($kode_pengembalian)
    {
        $this->selectedPengembalian = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
            ->where('kode_pengembalian', $kode_pengembalian)
            ->firstOrFail();
        
        $this->selectedDenda = Denda::where('peminjaman_id', $this->selectedPengembalian->peminjaman_id)->first();
        
        $this->showDetailModal = true;
    }

    // 🔹 Diperbarui: Pencarian menggunakan parameter $kode_pengembalian
    public function edit($kode_pengembalian)
    {
        abort_if(Gate::denies('update-pengembalian'), 403);
        $p = Pengembalian::where('kode_pengembalian', $kode_pengembalian)->firstOrFail();
        
        $this->editingKode = $kode_pengembalian;
        $this->peminjaman_id = $p->peminjaman_id;
        $this->tanggal_pengembalian = $p->tanggal_pengembalian;
        $this->kondisi_mobil_kembali = $p->kondisi_mobil;
        $this->catatan_pengembalian = $p->catatan;
        
        $this->isEditMode = true;
        $this->showCreateModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-pengembalian'), 403);

        $this->validate();

        $p = Pengembalian::where('kode_pengembalian', $this->editingKode)->firstOrFail();
        $p->update([
            'tanggal_pengembalian' => $this->tanggal_pengembalian,
            'kondisi_mobil' => trim($this->kondisi_mobil_kembali),
            'catatan' => trim($this->catatan_pengembalian),
        ]);

        $this->closeModal();
        $this->dispatch('notify', message: 'Data pengembalian diperbarui.', type: 'success');
    }

    // 🔹 Diperbarui: Pencarian menggunakan parameter $kode_pengembalian
    public function delete($kode_pengembalian)
    {
        abort_if(Gate::denies('delete-pengembalian'), 403);
        
        DB::beginTransaction();
        try {
            $p = Pengembalian::where('kode_pengembalian', $kode_pengembalian)->firstOrFail();
            
            $p->peminjaman()->update(['status' => 'berlangsung']);
            $p->peminjaman->mobil()->update(['status' => 'disewa']);
            
            $p->delete();
            DB::commit();
            $this->dispatch('notify', message: 'Data pengembalian dihapus, status mobil dikembalikan ke Disewa.', type: 'warning');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Gagal menghapus data.', type: 'error');
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
            'catatan_pengembalian', 'bukti_foto', 'editingKode', 'isEditMode',
            'selectedPengembalian', 'selectedDenda'
        ]);
        $this->resetErrorBag();
    }
}