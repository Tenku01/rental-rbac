<?php

namespace App\Livewire\Menu\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Pelanggan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class PelangganIndex extends Component
{
    use WithPagination;

    // --- Filters & Search ---
    public $search = '';
    public $filterStatus = ''; // aktif, tidak aktif

    // --- Modal States ---
    public $showModal = false;
    public $isEditMode = false;
    public $modalTitle = '';

    // --- Form Fields ---
    public $pelangganId;
    public $userId;
    public $nama;
    public $email;
    public $password;
    public $password_confirmation;
    public $no_telepon;
    public $alamat;
    public $status = 'aktif';

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    protected function rules()
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'no_telepon' => 'required|numeric|digits_between:10,15',
            'alamat' => 'required|string|max:500',
            'status' => 'required|in:aktif,tidak aktif',
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
        abort_if(Gate::denies('read-pelanggans'), 403, 'Akses ditolak.');

        $pelanggans = Pelanggan::with('user')
            ->when($this->search, function($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('no_telepon', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', fn($sq) => $sq->where('email', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('nama', 'asc')
            ->paginate(10);

        return view('livewire.menu.master.pelanggan-index', [
            'pelanggans' => $pelanggans
        ]);
    }

    // =========================================================================
    // CRUD: CREATE
    // =========================================================================

    public function create()
    {
        abort_if(Gate::denies('create-pelanggans'), 403);
        $this->resetInput();
        $this->isEditMode = false;
        $this->modalTitle = 'Tambah Pelanggan Baru';
        $this->showModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-pelanggans'), 403);
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

            // Assign Role (UserObserver akan membuat record Pelanggan dasar)
            $user->assignRole('pelanggan');

            // 2. Update Detail Pelanggan (Telepon & Alamat)
            // Menggunakan updateOrCreate agar aman jika observer sudah membuat recordnya
            Pelanggan::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama' => $this->nama,
                    'no_telepon' => $this->no_telepon,
                    'alamat' => $this->alamat,
                    'status' => $this->status
                ]
            );

            DB::commit();
            $this->closeModal();
            $this->dispatch('notify', message: 'Pelanggan berhasil didaftarkan.', type: 'success');

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
        abort_if(Gate::denies('update-pelanggans'), 403);
        
        $pelanggan = Pelanggan::with('user')->findOrFail($id);
        
        $this->pelangganId = $pelanggan->id;
        $this->userId = $pelanggan->user_id;
        $this->nama = $pelanggan->nama;
        $this->email = $pelanggan->user->email ?? '';
        $this->no_telepon = $pelanggan->no_telepon;
        $this->alamat = $pelanggan->alamat;
        $this->status = $pelanggan->status;
        
        $this->isEditMode = true;
        $this->modalTitle = 'Edit Data Pelanggan';
        $this->showModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-pelanggans'), 403);
        $this->validate();

        DB::beginTransaction();
        try {
            $pelanggan = Pelanggan::findOrFail($this->pelangganId);
            $user = User::findOrFail($this->userId);

            // Update User
            $userData = ['name' => $this->nama, 'email' => $this->email];
            if ($this->password) {
                $userData['password'] = Hash::make($this->password);
            }
            $user->update($userData);

            // Update Pelanggan
            $pelanggan->update([
                'nama' => $this->nama, 
                'no_telepon' => $this->no_telepon,
                'alamat' => $this->alamat,
                'status' => $this->status
            ]);

            DB::commit();
            $this->closeModal();
            $this->dispatch('notify', message: 'Data pelanggan diperbarui.', type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Gagal: ' . $e->getMessage(), type: 'error');
        }
    }

    // =========================================================================
    // CRUD: DELETE & TOGGLE
    // =========================================================================

    public function delete($id)
    {
        abort_if(Gate::denies('delete-pelanggans'), 403);
        
        $pelanggan = Pelanggan::findOrFail($id);
        
        if ($pelanggan->user) {
            $pelanggan->user->delete(); // Ini akan trigger observer deleted
        } else {
            $pelanggan->delete();
        }

        $this->dispatch('notify', message: 'Data pelanggan & akun dihapus.', type: 'warning');
    }

    public function toggleStatus($id)
    {
        abort_if(Gate::denies('update-pelanggans'), 403);
        $pelanggan = Pelanggan::findOrFail($id);
        $pelanggan->status = ($pelanggan->status === 'aktif') ? 'tidak aktif' : 'aktif';
        $pelanggan->save();
        $this->dispatch('notify', message: 'Status pelanggan diubah.', type: 'success');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->reset(['nama', 'email', 'password', 'password_confirmation', 'no_telepon', 'alamat', 'status', 'pelangganId', 'userId', 'isEditMode', 'modalTitle']);
        $this->resetErrorBag();
    }
}