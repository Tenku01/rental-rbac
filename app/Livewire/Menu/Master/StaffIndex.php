<?php

namespace App\Livewire\Menu\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffIndex extends Component
{
    use WithPagination;

    // Properti Input
    public $nama, $email, $password, $status;
    public $staffId, $userId;

    // State UI
    public $showModal = false;
    public $isEditMode = false;
    public $search = '';

    protected $paginationTheme = 'tailwind';

    // Rules Validasi
    protected function rules()
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'status' => 'required|in:aktif,tidak aktif',
        ];

        // Validasi Email Unik (Kecuali punya sendiri saat edit)
        if ($this->isEditMode) {
            $rules['email'] = ['required', 'email', Rule::unique('users')->ignore($this->userId)];
            $rules['password'] = 'nullable|min:6'; // Password opsional saat edit
        } else {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['password'] = 'required|min:6';
        }

        return $rules;
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $staffs = Staff::with('user')
            ->where('nama', 'like', '%' . $this->search . '%')
            ->orderBy('nama', 'asc')
            ->paginate(10);

        return view('livewire.admin.staff-index', [
            'staffs' => $staffs
        ]);
    }

    // --- CRUD ACTIONS ---

    public function create()
    {
        $this->resetInput();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        // 1. Buat Akun User Dulu
        $user = User::create([
            'name' => $this->nama,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => 5, // Role ID 5 = Staff
            'status' => 'aktif'
        ]);

        // 2. Buat Data Staff
        Staff::create([
            'user_id' => $user->id,
            'nama' => $this->nama,
            'status' => $this->status
        ]);

        $this->showModal = false;
        $this->resetInput();
        session()->flash('message', 'Staff baru berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $staff = Staff::with('user')->findOrFail($id);

        $this->staffId = $staff->id;
        $this->userId = $staff->user_id;
        $this->nama = $staff->nama;
        $this->email = $staff->user->email ?? '';
        $this->status = $staff->status;
        
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate();

        $staff = Staff::findOrFail($this->staffId);
        $user = User::findOrFail($this->userId);

        // 1. Update Data User (Login)
        $userData = [
            'name' => $this->nama,
            'email' => $this->email,
        ];
        // Hanya update password jika diisi
        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }
        $user->update($userData);

        // 2. Update Data Staff (Profile)
        $staff->update([
            'nama' => $this->nama,
            'status' => $this->status
        ]);

        $this->showModal = false;
        session()->flash('message', 'Data staff berhasil diperbarui.');
    }

    public function delete($id)
    {
        $staff = Staff::findOrFail($id);
        
        // Hapus usernya, otomatis staff terhapus karena ON DELETE CASCADE di database
        if ($staff->user) {
            $staff->user->delete();
        } else {
            $staff->delete();
        }

        session()->flash('message', 'Staff berhasil dihapus.');
    }

    private function resetInput()
    {
        $this->nama = '';
        $this->email = '';
        $this->password = '';
        $this->status = 'aktif';
        $this->staffId = null;
        $this->userId = null;
    }
}