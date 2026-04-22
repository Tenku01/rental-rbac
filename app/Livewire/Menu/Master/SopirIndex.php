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

#[Layout('layouts.admin')] 
class SopirIndex extends Component
{
    use WithPagination;

    // --- Filters & Search ---
    public $search = '';
    public $filterStatus = ''; 

    // --- Modal States ---
    public $showModal = false;
    public $showDetailModal = false;
    public $isEditMode = false;
    public $modalTitle = '';
    
    // --- Data Properties ---
    public $selectedSopir = null;

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

    /**
     * mount() dipanggil SEBELUM render().
     * Menggunakan redirect()->route('unauthorized') tanpa method layout()
     * agar tidak terjadi error pada lifecycle Livewire.
     */
    public function mount()
    {
        if (Gate::denies('read-sopirs')) {
            return redirect()->route('unauthorized');
        }
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    protected function rules()
    {
        $rules = [
            'nama' => 'required|string|max:255|regex:/^[a-zA-Z\s\.,\']+$/',
            'no_sim' => 'required|string|max:50|regex:/^[a-zA-Z0-9\-]+$/',
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

    protected $messages = [
        'nama.required' => 'Nama lengkap wajib diisi.',
        'nama.regex' => 'Nama hanya boleh berisi huruf dan tanda baca standar.',
        'no_sim.required' => 'Nomor SIM wajib diisi.',
        'no_sim.regex' => 'Format nomor SIM tidak valid.',
        'email.required' => 'Alamat email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        'password.required' => 'Password wajib diisi untuk akun baru.',
        'password.min' => 'Password minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
        'status.required' => 'Status wajib dipilih.',
    ];

    public function render()
    {
        // Fallback jika mount terlewati
        abort_if(Gate::denies('read-roles'), 403);

        $sopirs = Sopir::with('user')
            ->when($this->search, function($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('no_sim', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', fn($sq) => $sq->where('email', 'like', '%' . $this->search . '%'));
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('nama', 'asc')
            ->paginate(10);

        return view('livewire.menu.master.sopir-index', [
            'sopirs' => $sopirs
        ]);
    }

    public function create()
    {
        if (Gate::denies('create-sopirs')) {
            return redirect()->route('unauthorized');
        }

        $this->resetInput();
        $this->isEditMode = false;
        $this->modalTitle = 'Tambah Sopir Baru';
        $this->showModal = true;
    }

    public function store()
    {
        if (Gate::denies('create-sopirs')) {
            return redirect()->route('unauthorized');
        }

        $this->validate();

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => trim($this->nama),
                'email' => strtolower(trim($this->email)),
                'password' => Hash::make($this->password),
                'status' => 'aktif'
            ]);

            $user->assignRole('sopir');

            Sopir::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama' => trim($this->nama),
                    'no_sim' => strtoupper(trim($this->no_sim)),
                    'status' => $this->status
                ]
            );

            DB::commit();
            $this->closeModal();
            $this->dispatch('show-toast', message: 'Sopir berhasil didaftarkan.', type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', message: 'Terjadi kesalahan sistem.', type: 'error');
        }
    }

    public function edit($id)
    {
        if (Gate::denies('update-sopirs')) {
            return redirect()->route('unauthorized');
        }
        
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
        if (Gate::denies('update-sopirs')) {
            return redirect()->route('unauthorized');
        }

        $this->validate();

        DB::beginTransaction();
        try {
            $sopir = Sopir::findOrFail($this->sopirId);
            $user = User::findOrFail($this->userId);

            $userData = ['name' => trim($this->nama), 'email' => strtolower(trim($this->email))];
            if ($this->password) {
                $userData['password'] = Hash::make($this->password);
            }
            $user->update($userData);

            $sopir->update([
                'nama' => trim($this->nama), 
                'no_sim' => strtoupper(trim($this->no_sim)),
                'status' => $this->status
            ]);

            DB::commit();
            $this->closeModal();
            $this->dispatch('show-toast', message: 'Data sopir berhasil diperbarui.', type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-toast', message: 'Gagal memperbarui data.', type: 'error');
        }
    }

    public function delete($id)
    {
        if (Gate::denies('delete-sopirs')) {
            return redirect()->route('unauthorized');
        }
        
        try {
            $sopir = Sopir::findOrFail($id);
            if ($sopir->user) {
                $sopir->user->delete();
            } else {
                $sopir->delete();
            }
            $this->dispatch('show-toast', message: 'Data sopir & akun dihapus.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('show-toast', message: 'Gagal menghapus data.', type: 'error');
        }
    }

    public function showDetail($id)
    {
        $this->selectedSopir = Sopir::with(['user', 'peminjaman' => function($q) {
            $q->with('mobil')->orderBy('created_at', 'desc')->limit(10);
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
        $this->resetValidation();
    }
}