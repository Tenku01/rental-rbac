<?php

namespace App\Livewire\Menu\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class StaffIndex extends Component
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
    public $staffId;
    public $userId;
    public $nama;
    public $email;
    public $password;
    public $password_confirmation;
    public $status = 'aktif';

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    protected function rules()
    {
        $rules = [
            'nama' => 'required|string|max:255',
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
        abort_if(Gate::denies('read-staff'), 403, 'Akses ditolak.');

        $staffs = Staff::with('user')
            ->when($this->search, function($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', fn($sq) => $sq->where('email', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('nama', 'asc')
            ->paginate(10);

        return view('livewire.menu.master.staff-index', [
            'staffs' => $staffs
        ]);
    }

    // =========================================================================
    // CRUD: CREATE
    // =========================================================================

    public function create()
    {
        abort_if(Gate::denies('create-staff'), 403);
        $this->resetInput();
        $this->isEditMode = false;
        $this->modalTitle = 'Tambah Staff Baru';
        $this->showModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-staff'), 403);
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

            $user->assignRole('staff');

            // 2. Buat Profil Staff
            // Menggunakan updateOrCreate untuk menangani jika observer sudah membuat record duluan
            Staff::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama' => $this->nama,
                    'status' => $this->status
                ]
            );

            DB::commit();
            $this->closeModal();
            $this->dispatch('notify', message: 'Staff berhasil ditambahkan.', type: 'success');

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
        abort_if(Gate::denies('update-staff'), 403);
        
        $staff = Staff::with('user')->findOrFail($id);
        
        $this->staffId = $staff->id;
        $this->userId = $staff->user_id;
        $this->nama = $staff->nama;
        $this->email = $staff->user->email ?? '';
        $this->status = $staff->status;
        
        $this->isEditMode = true;
        $this->modalTitle = 'Edit Data Staff';
        $this->showModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-staff'), 403);
        $this->validate();

        DB::beginTransaction();
        try {
            $staff = Staff::findOrFail($this->staffId);
            $user = User::findOrFail($this->userId);

            // Update User
            $userData = ['name' => $this->nama, 'email' => $this->email];
            if ($this->password) {
                $userData['password'] = Hash::make($this->password);
            }
            $user->update($userData);

            // Update Staff
            $staff->update([
                'nama' => $this->nama, 
                'status' => $this->status
            ]);

            DB::commit();
            $this->closeModal();
            $this->dispatch('notify', message: 'Data staff diperbarui.', type: 'success');

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
        abort_if(Gate::denies('delete-staff'), 403);
        
        $staff = Staff::findOrFail($id);
        
        if ($staff->user) {
            $staff->user->delete();
        } else {
            $staff->delete();
        }

        $this->dispatch('notify', message: 'Data staff & akun dihapus.', type: 'warning');
    }

    public function toggleStatus($id)
    {
        abort_if(Gate::denies('update-staff'), 403);
        $staff = Staff::findOrFail($id);
        $staff->status = ($staff->status === 'aktif') ? 'tidak aktif' : 'aktif';
        $staff->save();
        $this->dispatch('notify', message: 'Status staff diubah.', type: 'success');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->reset(['nama', 'email', 'password', 'password_confirmation', 'status', 'staffId', 'userId', 'isEditMode', 'modalTitle']);
        $this->resetErrorBag();
    }
}