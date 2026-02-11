<?php

namespace App\Livewire\Menu\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Resepsionis;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ResepsionisIndex extends Component
{
    use WithPagination;

    public $nama, $email, $password, $status;
    public $resepsionisId, $userId;
    public $showModal = false;
    public $isEditMode = false;
    public $search = '';

    protected $paginationTheme = 'tailwind';

    protected function rules()
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'status' => 'required|in:aktif,tidak aktif',
        ];

        if ($this->isEditMode) {
            $rules['email'] = ['required', 'email', Rule::unique('users')->ignore($this->userId)];
            $rules['password'] = 'nullable|min:6';
        } else {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['password'] = 'required|min:6';
        }

        return $rules;
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $resepsionis = Resepsionis::with('user')
            ->where('nama', 'like', '%' . $this->search . '%')
            ->orderBy('nama', 'asc')
            ->paginate(10);

        return view('livewire.admin.resepsionis-index', [
            'resepsionis' => $resepsionis
        ]);
    }

    public function create()
    {
        $this->resetInput();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate();

        // 1. Buat User (Role ID 3 = Resepsionis)
        $user = User::create([
            'name' => $this->nama,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role_id' => 3, 
            'status' => 'aktif'
        ]);

        // 2. Buat Resepsionis
        Resepsionis::create([
            'user_id' => $user->id,
            'nama' => $this->nama,
            'status' => $this->status
        ]);

        $this->showModal = false;
        $this->resetInput();
        session()->flash('message', 'Resepsionis berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $resep = Resepsionis::with('user')->findOrFail($id);
        $this->resepsionisId = $resep->id;
        $this->userId = $resep->user_id;
        $this->nama = $resep->nama;
        $this->email = $resep->user->email ?? '';
        $this->status = $resep->status;
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate();
        $resep = Resepsionis::findOrFail($this->resepsionisId);
        $user = User::findOrFail($this->userId);

        $userData = ['name' => $this->nama, 'email' => $this->email];
        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }
        $user->update($userData);

        $resep->update(['nama' => $this->nama, 'status' => $this->status]);

        $this->showModal = false;
        session()->flash('message', 'Data resepsionis diperbarui.');
    }

    public function delete($id)
    {
        $resep = Resepsionis::findOrFail($id);
        if ($resep->user) $resep->user->delete();
        else $resep->delete();
        session()->flash('message', 'Resepsionis dihapus.');
    }

    private function resetInput()
    {
        $this->nama = ''; $this->email = ''; $this->password = ''; 
        $this->status = 'aktif'; $this->resepsionisId = null; $this->userId = null;
    }
}