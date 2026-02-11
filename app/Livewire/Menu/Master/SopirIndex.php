<?php

namespace App\Livewire\Menu\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Sopir;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SopirIndex extends Component
{
    use WithPagination;

    // Properti Input
    public $nama, $email, $password, $no_sim, $status;
    public $sopirId, $userId;

    // State UI
    public $showModal = false;
    public $isEditMode = false;
    public $search = '';
    public $filterStatus = '';

    protected $paginationTheme = 'tailwind';

    // Rules Validasi
    protected function rules()
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'no_sim' => 'required|string|max:20', // Wajib ada SIM
            'status' => 'required|in:tersedia,tidak tersedia,bekerja',
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
        $sopirs = Sopir::with('user')
            ->when($this->search, function($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('no_sim', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterStatus, function($q) {
                $q->where('status', $this->filterStatus);
            })
            ->orderBy('nama', 'asc')
            ->paginate(10);

        return view('livewire.admin.sopir-index', [
            'sopirs' => $sopirs
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

        // 1. Buat Akun User (Role ID 4 = Sopir)
        $user = User::create([
            'name' => $this->nama,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => 4, // Role Sopir
            'status' => 'aktif'
        ]);

        // 2. Buat Data Profil Sopir
        Sopir::create([
            'user_id' => $user->id,
            'nama' => $this->nama,
            'no_sim' => $this->no_sim,
            'status' => $this->status
        ]);

        $this->showModal = false;
        $this->resetInput();
        session()->flash('message', 'Data sopir berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $sopir = Sopir::with('user')->findOrFail($id);

        $this->sopirId = $sopir->id;
        $this->userId = $sopir->user_id;
        $this->nama = $sopir->nama;
        $this->no_sim = $sopir->no_sim;
        $this->email = $sopir->user->email ?? '';
        $this->status = $sopir->status;
        
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate();

        $sopir = Sopir::findOrFail($this->sopirId);
        $user = User::findOrFail($this->userId);

        // 1. Update User Login
        $userData = [
            'name' => $this->nama,
            'email' => $this->email,
        ];
        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }
        $user->update($userData);

        // 2. Update Profil Sopir
        $sopir->update([
            'nama' => $this->nama,
            'no_sim' => $this->no_sim,
            'status' => $this->status
        ]);

        $this->showModal = false;
        session()->flash('message', 'Data sopir berhasil diperbarui.');
    }

    public function delete($id)
    {
        $sopir = Sopir::findOrFail($id);
        
        // Hapus usernya, otomatis data sopir terhapus (Cascade)
        if ($sopir->user) {
            $sopir->user->delete();
        } else {
            $sopir->delete();
        }

        session()->flash('message', 'Data sopir berhasil dihapus.');
    }

    private function resetInput()
    {
        $this->nama = '';
        $this->email = '';
        $this->password = '';
        $this->no_sim = '';
        $this->status = 'tersedia';
        $this->sopirId = null;
        $this->userId = null;
    }
}