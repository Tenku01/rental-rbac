<?php

namespace App\Livewire\Menu\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\User;
// use App\Models\UserIdentification; // Dihapus karena tabel sudah tidak ada
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;

class UserIndex extends Component
{
    use WithPagination;
    use WithFileUploads;

    // State Filter & Search
    public $search = '';
    public $filterRole = '';
    
    // Modal state
    public $showModal = false;
    public $modalTitle = '';
    public $editingUserId = null;
    
    // Form fields - User Core
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $selectedRole = ''; 
    public $status = 'aktif';

    // Form fields - Detail Profil & Identitas
    public $alamat = '';
    public $no_telepon = '';
    public $no_sim_sopir = ''; 
    
    // File Uploads
    public $ktp_file;
    public $sim_file;

    protected $paginationTheme = 'tailwind';

        public function mount()
        {
            if (Gate::denies('read-users')) {
                return redirect()->route('unauthorized');
            }
        }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterRole() { $this->resetPage(); }

    public function updatedSelectedRole()
    {
        $this->resetValidation();
    }

    // Hook untuk validasi real-time saat user mengetik
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

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

        if ($this->selectedRole === 'pelanggan') {
            $rules['alamat'] = 'required|string|max:255';
            $rules['no_telepon'] = 'required|numeric';
            
            if (!$this->editingUserId) {
                $rules['ktp_file'] = 'required|image|max:5120'; // Disesuaikan ke 5MB
                $rules['sim_file'] = 'required|image|max:5120';
            } else {
                $rules['ktp_file'] = 'nullable|image|max:5120';
                $rules['sim_file'] = 'nullable|image|max:5120';
            }
        }

        if ($this->selectedRole === 'sopir') {
            $rules['no_sim_sopir'] = 'required|string|max:50';
        }

        return $rules;
    }

    // Menambahkan custom message dalam bahasa Indonesia yang ramah
    protected function messages()
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.string' => 'Format nama tidak valid.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email harus valid (contoh: budi@gmail.com).',
            'email.unique' => 'Email ini sudah terdaftar di sistem.',
            'selectedRole.required' => 'Peran (Role) wajib dipilih.',
            'selectedRole.exists' => 'Peran yang dipilih tidak valid di sistem.',
            'status.required' => 'Status keaktifan wajib dipilih.',
            'status.in' => 'Pilihan status tidak valid.',
            'password.required' => 'Password wajib diisi untuk pengguna baru.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal harus 8 karakter.',
            
            // Pesan khusus Pelanggan
            'alamat.required' => 'Alamat lengkap wajib diisi untuk role pelanggan.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
            'no_telepon.numeric' => 'Nomor telepon hanya boleh berisi angka.',
            'ktp_file.required' => 'Foto KTP wajib diunggah untuk pelanggan baru.',
            'ktp_file.image' => 'File KTP harus berupa gambar.',
            'ktp_file.max' => 'Ukuran file KTP maksimal 5MB.',
            'sim_file.required' => 'Foto SIM wajib diunggah untuk pelanggan baru.',
            'sim_file.image' => 'File SIM harus berupa gambar.',
            'sim_file.max' => 'Ukuran file SIM maksimal 5MB.',
            
            // Pesan khusus Sopir
            'no_sim_sopir.required' => 'Nomor SIM wajib diisi untuk role sopir.',
        ];
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

        $roles = Role::orderBy('name', 'asc')->get();

        $existingAdmin = User::role('admin')->first();
        $existingAdminId = $existingAdmin ? $existingAdmin->id : null;

        return view('livewire.menu.master.user-index', [
            'users' => $users,
            'roles' => $roles,
            'existingAdminId' => $existingAdminId 
        ]);
    }

    public function create()
    {
        abort_if(Gate::denies('create-users'), 403);
        $this->resetForm();
        $this->modalTitle = 'Registrasi Personel / Pengguna Baru';
        $this->showModal = true;
    }

    public function edit($id)
    {
        abort_if(Gate::denies('update-users'), 403);
        
        // Relasi pelanggan dihapus karena atributnya sekarang di users
        $user = User::with(['sopir'])->findOrFail($id);
        
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->status = $user->status;
        $this->selectedRole = $user->getRoleNames()->first();
        
        if ($this->selectedRole === 'pelanggan') {
            // Ambil langsung dari object user
            $this->alamat = $user->alamat;
            $this->no_telepon = $user->no_telepon;
        } elseif ($this->selectedRole === 'sopir' && $user->sopir) {
            $this->no_sim_sopir = $user->sopir->no_sim;
        }
        
        $this->modalTitle = 'Ubah Informasi Pengguna';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->selectedRole === 'admin') {
            $adminQuery = User::role('admin');
            if ($this->editingUserId) {
                $adminQuery->where('id', '!=', $this->editingUserId);
            }
            if ($adminQuery->exists()) {
                $this->addError('selectedRole', 'Sistem dibatasi hanya boleh memiliki 1 akun Administrator Utama.');
                return; 
            }
        }

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
        ];

        // Jika role pelanggan, sertakan alamat & no telepon ke dalam array user update/create
        if ($this->selectedRole === 'pelanggan') {
            $data['alamat'] = $this->alamat;
            $data['no_telepon'] = $this->no_telepon;
        }

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
            $user->syncRoles([$this->selectedRole]); 
            $user->touch(); 
            
            $message = 'Informasi pengguna berhasil diperbarui.';
        } else {
            if ($this->selectedRole !== 'pelanggan') {
                $data['email_verified_at'] = now();
            }
            // Tambahkan status verifikasi awal untuk pengguna baru ber-role pelanggan
            if ($this->selectedRole === 'pelanggan') {
                $data['status_verifikasi'] = 'belum_upload';
            }

            $user = User::create($data);
            event(new Registered($user));
            $user->syncRoles([$this->selectedRole]); 
            
            $user->touch(); 
            
            $message = 'Pengguna baru berhasil didaftarkan.';
        }

        // --- Update Detail Profil KTP/SIM langsung di Users ---
        if ($this->selectedRole === 'pelanggan') {
            if ($this->ktp_file || $this->sim_file) {
                
                if ($this->ktp_file) {
                    if ($user->foto_ktp && Storage::disk('public')->exists($user->foto_ktp)) {
                        Storage::disk('public')->delete($user->foto_ktp);
                    }
                    $user->foto_ktp = $this->ktp_file->store('ktp', 'public');
                }

                if ($this->sim_file) {
                    if ($user->foto_sim && Storage::disk('public')->exists($user->foto_sim)) {
                        Storage::disk('public')->delete($user->foto_sim);
                    }
                    $user->foto_sim = $this->sim_file->store('sim', 'public');
                }

                $user->status_verifikasi = 'menunggu'; 
                $user->alasan_penolakan = null;
                $user->save();
            }

        } elseif ($this->selectedRole === 'sopir') {
            // Logika sopir dibiarkan karena tabel sopirs tidak di-drop
            $user->sopir()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'no_sim' => $this->no_sim_sopir
                ]
            );
        }

        $this->closeModal();
        $this->dispatch('notify', message: $message, type: 'success');
    }

    public function delete($id)
    {
        abort_if(Gate::denies('delete-users'), 403);
        $user = User::findOrFail($id);

        if ($user->hasRole('admin')) {
            $this->dispatch('notify', message: 'Demi keamanan, Akun Administrator Utama tidak dapat dihapus!', type: 'error');
            return;
        }

        if ($user->id === Auth::id()) {
            $this->dispatch('notify', message: 'Akun aktif tidak bisa dihapus!', type: 'error');
            return;
        }

        // Cek dan hapus foto identitas milik user sebelum datanya dihapus
        if ($user->foto_ktp) Storage::disk('public')->delete($user->foto_ktp);
        if ($user->foto_sim) Storage::disk('public')->delete($user->foto_sim);

        $user->delete();
        $this->dispatch('notify', message: 'User berhasil dihapus dari sistem.', type: 'success');
    }

    public function toggleStatus($id)
    {
        abort_if(Gate::denies('update-users'), 403);
        $user = User::findOrFail($id);
        
        if ($user->hasRole('admin')) {
            $this->dispatch('notify', message: 'Status Administrator Utama tidak dapat diubah!', type: 'error');
            return;
        }

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
        $this->reset([
            'editingUserId', 'name', 'email', 'password', 'password_confirmation', 
            'selectedRole', 'status', 'alamat', 'no_telepon', 'no_sim_sopir', 
            'ktp_file', 'sim_file'
        ]);
        $this->status = 'aktif';
        $this->resetErrorBag();
    }
}