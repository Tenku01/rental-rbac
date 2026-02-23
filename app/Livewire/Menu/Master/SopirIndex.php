<?php

namespace App\Livewire\Menu\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Sopir;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class SopirIndex extends Component
{
    use WithPagination;

    // --- Filters & Search ---
    public $search = '';
    public $filterStatus = ''; // Tersedia, Bekerja, Tidak Tersedia

    // --- Modal States ---
    public $showModal = false;
    public $showDetailModal = false; // Tambahan untuk modal detail
    public $isEditMode = false;
    public $modalTitle = '';
    
    // --- Data Properties ---
    public $selectedSopir = null; // Menyimpan data sopir yang dilihat detailnya

    // --- Form Fields ---
    public $sopirId;
    public $userId;
    public $nama;
    public $email;
    public $password;
    public $password_confirmation;
    public $no_sim;
    public $status = 'Tersedia';

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    protected function rules()
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'no_sim' => 'required|string|max:50',
            'status' => 'required|in:Tersedia,Bekerja,Tidak Tersedia',
        ];

        if ($this->isEditMode) {
            $rules['email'] = ['required', 'email', Rule::unique('users', 'email')->ignore($this->userId)];
            $rules['password'] = 'nullable|confirmed|min:8';
        } else {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['password'] = 'required|confirmed|min:8';
        }

        return $rules;
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        abort_if(Gate::denies('read-sopir'), 403, 'Akses ditolak.');

        $sopirs = Sopir::with('user')
            ->when($this->search, function($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('no_sim', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', fn($sq) => $sq->where('email', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('status', 'asc') // Urutkan status agar yang 'Bekerja' atau 'Tersedia' di atas
            ->orderBy('nama', 'asc')
            ->paginate(10);

        return view('livewire.menu.master.sopir-index', [
            'sopirs' => $sopirs
        ]);
    }

    // =========================================================================
    // CRUD: CREATE
    // =========================================================================

    public function create()
    {
        abort_if(Gate::denies('create-sopir'), 403);
        $this->resetInput();
        $this->isEditMode = false;
        $this->modalTitle = 'Tambah Sopir Baru';
        $this->showModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-sopir'), 403);
        $this->validate();

        DB::beginTransaction();
        try {
            // 1. Buat User
            $user = User::create([
                'name' => $this->nama,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'status' => 'aktif'
            ]);

            $user->assignRole('sopir');

            // 2. Buat/Update Data Sopir (Menggunakan updateOrCreate untuk handle observer)
            Sopir::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama' => $this->nama,
                    'no_sim' => $this->no_sim,
                    'status' => $this->status
                ]
            );

            DB::commit();
            $this->closeModal();
            $this->dispatch('notify', message: 'Sopir berhasil ditambahkan.', type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Gagal: ' . $e->getMessage(), type: 'error');
        }
    }

    // =========================================================================
    // CRUD: UPDATE
    // =========================================================================

    public function edit($id)
    {
        abort_if(Gate::denies('update-sopir'), 403);
        
        $sopir = Sopir::with('user')->findOrFail($id);
        
        $this->sopirId = $sopir->id;
        $this->userId = $sopir->user_id;
        $this->nama = $sopir->nama;
        $this->email = $sopir->user->email ?? '';
        $this->no_sim = $sopir->no_sim;
        $this->status = $sopir->status;
        
        $this->isEditMode = true;
        $this->modalTitle = 'Edit Data Sopir';
        $this->showModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-sopir'), 403);
        $this->validate();

        DB::beginTransaction();
        try {
            $sopir = Sopir::findOrFail($this->sopirId);
            $user = User::findOrFail($this->userId);

            // Update User
            $userData = ['name' => $this->nama, 'email' => $this->email];
            if ($this->password) {
                $userData['password'] = Hash::make($this->password);
            }
            $user->update($userData);

            // Update Sopir
            $sopir->update([
                'nama' => $this->nama, 
                'no_sim' => $this->no_sim,
                'status' => $this->status
            ]);

            DB::commit();
            $this->closeModal();
            $this->dispatch('notify', message: 'Data sopir diperbarui.', type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Gagal: ' . $e->getMessage(), type: 'error');
        }
    }

    // =========================================================================
    // CRUD: DELETE
    // =========================================================================

    public function delete($id)
    {
        abort_if(Gate::denies('delete-sopir'), 403);
        
        $sopir = Sopir::findOrFail($id);
        
        // Hapus akun user terkait (Cascade delete di DB biasanya handle sisanya)
        if ($sopir->user) {
            $sopir->user->delete();
        } else {
            $sopir->delete();
        }

        $this->dispatch('notify', message: 'Data sopir & akun dihapus.', type: 'warning');
    }

    // =========================================================================
    // CRUD: SHOW DETAIL (NEW)
    // =========================================================================

    public function showDetail($id)
    {
        // Eager load relasi peminjaman beserta detail mobil untuk tabel riwayat
        $this->selectedSopir = Sopir::with(['user', 'peminjaman' => function($q) {
            $q->with('mobil')->orderBy('created_at', 'desc')->limit(10); // Ambil 10 tugas terakhir
        }])->findOrFail($id);

        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showDetailModal = false;
        $this->selectedSopir = null;
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->reset(['nama', 'email', 'password', 'password_confirmation', 'no_sim', 'status', 'sopirId', 'userId', 'isEditMode', 'modalTitle']);
        $this->resetErrorBag();
    }
}