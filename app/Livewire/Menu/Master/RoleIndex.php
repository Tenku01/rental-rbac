<?php

namespace App\Livewire\Menu\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RoleIndex extends Component
{
    use WithPagination;

    // State untuk Form & Modal
    public $roleId;
    public $role_name;
    public $selectedPermissions = []; 
    public $showModal = false;
    public $isEditMode = false;
    public $modalTitle = '';
    public $searchPermission = ''; 

    protected $paginationTheme = 'tailwind';

 public function mount()
    {
        if (Gate::denies('read-roles')) {
            return redirect()->route('unauthorized');
        }
    }

    // --- Validasi Realtime ---
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    

    protected function rules()
    {
        return [
            'role_name' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_\s]+$/', // Alfanumerik, underscore, dan spasi
                Rule::unique('roles', 'name')->ignore($this->roleId),
            ],
            'selectedPermissions' => 'required|array|min:1'
        ];
    }

    protected $messages = [
        'role_name.required' => 'Nama peranan wajib diisi.',
        'role_name.min' => 'Nama peranan minimal 3 karakter.',
        'role_name.unique' => 'Nama peranan ini sudah terdaftar.',
        'role_name.regex' => 'Nama peranan hanya boleh huruf, angka, spasi, atau underscore.',
        'selectedPermissions.required' => 'Pilih minimal satu izin akses.',
        'selectedPermissions.min' => 'Role harus memiliki setidaknya satu hak akses operasional.'
    ];

    #[Layout('layouts.admin')]
    public function render()
    {
        abort_if(Gate::denies('read-roles'), 403);

        $roles = Role::where('name', '!=', 'pelanggan')
            ->with('permissions')
            ->orderBy('id', 'asc')
            ->get();

        $permissions = Permission::where('name', 'like', '%' . $this->searchPermission . '%')
            ->orderBy('name', 'asc')
            ->get();

        $groupedPermissions = $permissions->groupBy(function($perm) {
            $parts = explode('-', $perm->name);
            return $parts[1] ?? 'lainnya (Menu Sidebar)';
        });

        return view('livewire.menu.master.role-index', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions
        ]);
    }

    public function create()
    {
        abort_if(Gate::denies('create-roles'), 403);
        $this->resetForm();
        $this->isEditMode = false;
        $this->modalTitle = 'Tambah Peranan Baru';
        $this->showModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-roles'), 403);
        $this->validate();

        $role = Role::create(['name' => strtolower(trim($this->role_name))]);
        $role->syncPermissions($this->selectedPermissions);

        $this->showModal = false;
        $this->dispatch('show-toast', message: 'Peranan baru berhasil ditambahkan.', type: 'success');
        $this->forgetCache();
    }

    public function edit($id)
    {
        abort_if(Gate::denies('update-roles'), 403);
        
        $role = Role::find($id);

        if (!$role || $role->name === 'pelanggan') {
            $this->dispatch('show-toast', message: 'Aksi ditolak atau data tidak ditemukan.', type: 'error');
            return;
        }
        
        $this->resetForm();
        $this->roleId = $role->id;
        $this->role_name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        
        $this->isEditMode = true;
        $this->modalTitle = 'Konfigurasi Otoritas';
        $this->showModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-roles'), 403);
        $this->validate();

        $role = Role::find($this->roleId);

        if ($role->id === 1 && $this->role_name !== 'admin') {
            $this->dispatch('show-toast', message: 'Nama Role Admin Utama tidak boleh diubah!', type: 'error');
            return;
        }

        $role->update(['name' => strtolower(trim($this->role_name))]);
        $role->syncPermissions($this->selectedPermissions);

        $this->showModal = false;
        $this->dispatch('show-toast', message: 'Otoritas peranan berhasil diperbarui.', type: 'success');
        $this->forgetCache();
    }

    public function delete($id)
    {
        abort_if(Gate::denies('delete-roles'), 403);

        $role = Role::findOrFail($id);

        // Proteksi: Admin Utama & Role yang masih dipakai user tidak boleh dihapus
        if ($role->id === 1 || $role->name === 'admin') {
            $this->dispatch('show-toast', message: 'Role Admin Utama bersifat permanen.', type: 'error');
            return;
        }

        if ($role->users()->count() > 0) {
            $this->dispatch('show-toast', message: 'Gagal: Role ini masih digunakan oleh beberapa personel.', type: 'error');
            return;
        }

        $role->delete();
        $this->dispatch('show-toast', message: 'Peranan berhasil dihapus dari sistem.', type: 'success');
        $this->forgetCache();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function forgetCache()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function resetForm()
    {
        $this->reset(['roleId', 'role_name', 'selectedPermissions', 'searchPermission', 'isEditMode', 'modalTitle']);
        $this->resetErrorBag();
    }
}