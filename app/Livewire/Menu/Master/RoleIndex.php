<?php

namespace App\Livewire\Menu\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;

class RoleIndex extends Component
{
    use WithPagination;

    // State untuk Form & Modal
    public $roleId;
    public $role_name;
    public $selectedPermissions = []; // Penampung checklist izin
    public $showModal = false;
    public $searchPermission = ''; // Filter pencarian izin di dalam modal

    protected $paginationTheme = 'tailwind';

    #[Layout('layouts.admin')]
    public function render()
    {
        // Proteksi Halaman: Minimal memiliki izin read-roles
        abort_if(Gate::denies('read-roles'), 403);

        // 1. Ambil semua role KECUALI 'pelanggan' karena hak aksesnya sudah paten (hardcoded)
        $roles = Role::where('name', '!=', 'pelanggan')
            ->with('permissions')
            ->orderBy('id', 'asc')
            ->get();

        // 2. Ambil semua permission untuk ditampilkan di checklist modal
        $permissions = Permission::where('name', 'like', '%' . $this->searchPermission . '%')
            ->orderBy('name', 'asc')
            ->get();

        // 3. Mengelompokkan permission berdasarkan kategori untuk hierarki visual di UI
        $groupedPermissions = $permissions->groupBy(function($perm) {
            $parts = explode('-', $perm->name);
            return $parts[1] ?? 'lainnya';
        });

        return view('livewire.menu.master.role-index', [
            'roles' => $roles,
            'groupedPermissions' => $groupedPermissions
        ]);
    }

    /**
     * Membuka form edit untuk role tertentu
     */
    public function edit($id)
    {
        // Pastikan izin update ada atau user adalah admin (bypass via Gate::before)
        abort_if(Gate::denies('update-roles'), 403);
        
        $role = Role::find($id);

        if (!$role) {
            $this->dispatch('notify', message: 'Data peranan tidak ditemukan.', type: 'error');
            return;
        }

        // Proteksi: Role pelanggan tidak boleh diedit via sistem dinamis
        if ($role->name === 'pelanggan') {
            $this->dispatch('notify', message: 'Role pelanggan bersifat paten.', type: 'error');
            return;
        }
        
        // Reset form sebelum mengisi data baru
        $this->resetForm();
        
        $this->roleId = $role->id;
        $this->role_name = $role->name;
        
        // Ambil izin yang sudah dimiliki role ini
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        
        // Set modal menjadi true
        $this->showModal = true;
    }

    /**
     * Menutup Modal dan Reset Form
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    /**
     * Sinkronisasi data Role dan Permission
     */
    public function update()
    {
        abort_if(Gate::denies('update-roles'), 403);

        $this->validate([
            'role_name' => 'required|string|max:50|unique:roles,name,' . $this->roleId,
            'selectedPermissions' => 'required|array|min:1'
        ]);

        $role = Role::find($this->roleId);

        if (!$role) {
            $this->showModal = false;
            return;
        }

        // Proteksi Final: Nama Role 'admin' (ID 1) atau 'pelanggan' tidak boleh diubah
        if ($role->name === 'pelanggan') {
            $this->dispatch('notify', message: 'Role pelanggan tidak dapat dimodifikasi.', type: 'error');
            $this->showModal = false;
            return;
        }

        if ($role->id === 1 && $this->role_name !== 'admin') {
            $this->dispatch('notify', message: 'Nama Role Admin Utama tidak boleh diubah!', type: 'error');
            return;
        }

        // 1. Update Nama Role
        $role->update(['name' => $this->role_name]);

        // 2. Sinkronisasi Izin (Checklist)
        $role->syncPermissions($this->selectedPermissions);

        $this->showModal = false;
        $this->dispatch('notify', message: 'Otoritas peranan berhasil diperbarui.', type: 'success');
        
        // Clear cache Spatie agar perubahan hak akses langsung aktif bagi user
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    private function resetForm()
    {
        $this->reset(['roleId', 'role_name', 'selectedPermissions', 'searchPermission']);
        $this->resetErrorBag();
    }
}