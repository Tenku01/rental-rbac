<?php

namespace App\Livewire\Menu\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules;

class UserIndex extends Component
{
    use WithPagination;

    // State Filter & Search
    public $search = '';
    public $filterRole = '';
    
    // Modal state
    public $showModal = false;
    public $modalTitle = '';
    public $editingUserId = null;
    
    // Form fields
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $selectedRole = ''; 
    public $status = 'aktif';

    protected $paginationTheme = 'tailwind';

    /**
     * Sinkronisasi data saat pencarian atau filter berubah
     */
    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterRole() { $this->resetPage(); }

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $this->editingUserId,
            'selectedRole' => 'required|exists:roles,name',
            'status' => 'required|in:aktif,nonaktif',
        ];

        if (!$this->editingUserId) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        } else {
            $rules['password'] = 'nullable|confirmed|min:8';
        }

        return $rules;
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        abort_if(Gate::denies('read-users'), 403);

        $users = User::with('roles')
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('name', 'like', '%'.$this->search.'%')
                       ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterRole, function($q) {
                $q->role($this->filterRole);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Ambil semua role kecuali pelanggan untuk pilihan di filter/form
        $roles = Role::where('name', '!=', 'pelanggan')->orderBy('name', 'asc')->get();

        return view('livewire.menu.master.user-index', [
            'users' => $users,
            'roles' => $roles
        ]);
    }

    public function create()
    {
        abort_if(Gate::denies('create-users'), 403);
        $this->resetForm();
        $this->modalTitle = 'Registrasi Personel Baru';
        $this->showModal = true;
    }

    public function edit($id)
    {
        abort_if(Gate::denies('update-users'), 403);
        
        $user = User::findOrFail($id);
        
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->status = $user->status;
        $this->selectedRole = $user->getRoleNames()->first();
        
        $this->modalTitle = 'Ubah Informasi Personel';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            
            if ($user->id === Auth::id() && $this->status === 'nonaktif') {
                $this->dispatch('notify', message: 'Dilarang menonaktifkan akun sendiri!', type: 'error');
                return;
            }
            
            $user->update($data);
            $user->syncRoles($this->selectedRole);
            $message = 'Informasi personel berhasil diperbarui.';
        } else {
            $user = User::create($data);
            $user->assignRole($this->selectedRole);
            $message = 'Personel baru berhasil didaftarkan.';
        }

        $this->closeModal();
        $this->dispatch('notify', message: $message, type: 'success');
    }

    public function delete($id)
    {
        abort_if(Gate::denies('delete-users'), 403);
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            $this->dispatch('notify', message: 'Akun aktif tidak bisa dihapus!', type: 'error');
            return;
        }

        $user->delete();
        $this->dispatch('notify', message: 'User berhasil dihapus dari sistem.', type: 'success');
    }

    public function toggleStatus($id)
    {
        abort_if(Gate::denies('update-users'), 403);
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) return;

        $user->status = ($user->status === 'aktif') ? 'nonaktif' : 'aktif';
        $user->save();

        $this->dispatch('notify', message: 'Status user berhasil diubah.', type: 'success');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset(['editingUserId', 'name', 'email', 'password', 'password_confirmation', 'selectedRole', 'status']);
        $this->status = 'aktif';
        $this->resetErrorBag();
    }
}