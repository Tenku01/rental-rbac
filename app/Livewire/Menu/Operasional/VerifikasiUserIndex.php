<?php

namespace App\Livewire\Menu\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads; 
use Livewire\Attributes\Layout;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VerifikasiUserIndex extends Component
{
    use WithPagination;
    use WithFileUploads; 

    // State Filter & Search
    public $filterStatus = 'menunggu'; 
    public $search = '';

    // Modal State (Form)
    public $showModal = false;
    public $modalTitle = '';
    public $isEditMode = false;
    public $editingId = null;

    // Modal State (Detail)
    public $showDetailModal = false;
    public $detailData = null;

    // Form Fields
    public $user_id;
    public $ktp_file;
    public $sim_file;
    public $status_verifikasi = 'menunggu'; // Disesuaikan dari status_approval
    
    // Preview file lama saat edit
    public $existing_ktp;
    public $existing_sim;

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        // Tetap menggunakan permission lama agar tidak merusak database Spatie
        if (Gate::denies('read-user_identifications')) {
            return redirect()->route('unauthorized');
        }
    }

    // Reset pagination saat filter berubah
    public function updatedFilterStatus() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    // Standar: Validasi dinamis
    protected function rules()
    {
        $rules = [
            'status_verifikasi' => 'required|in:menunggu,disetujui,ditolak',
        ];

        if ($this->isEditMode) {
            $rules['user_id'] = 'required|exists:users,id';
            $rules['ktp_file'] = 'nullable|image|max:5120'; // Diubah jadi 5MB
            $rules['sim_file'] = 'nullable|image|max:5120';
        } else {
            $rules['user_id'] = 'required|exists:users,id';
            $rules['ktp_file'] = 'required|image|max:5120';
            $rules['sim_file'] = 'required|image|max:5120'; 
        }

        return $rules;
    }

    // Standar: Pesan error Bahasa Indonesia
    protected function messages()
    {
        return [
            'user_id.required' => 'Pelanggan wajib dipilih.',
            'user_id.exists' => 'Pelanggan tidak valid atau tidak ditemukan.',
            'ktp_file.required' => 'Dokumen KTP wajib diunggah.',
            'ktp_file.image' => 'File KTP harus berupa gambar (JPG, PNG).',
            'ktp_file.max' => 'Ukuran file KTP maksimal 5MB.',
            'sim_file.required' => 'Dokumen SIM wajib diunggah.',
            'sim_file.image' => 'File SIM harus berupa gambar (JPG, PNG).',
            'sim_file.max' => 'Ukuran file SIM maksimal 5MB.',
            'status_verifikasi.required' => 'Status verifikasi wajib ditentukan.',
            'status_verifikasi.in' => 'Status tidak dikenali.',
        ];
    }

    // Validasi Real-time
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        // Langsung query dari model User (role pelanggan)
        $identitas = User::role('pelanggan')
            ->when($this->search, function($q) {
                $q->where(function($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function($q) {
                // Filter berdasarkan status
                return $q->where('status_verifikasi', $this->filterStatus);
            }, function($q) {
                // Jika tidak ada filter status yang dipilih, sembunyikan yang 'belum_upload'
                return $q->where('status_verifikasi', '!=', 'belum_upload');
            })
            ->orderBy('updated_at', 'desc') // Pakai updated_at karena user baru saja mengupload
            ->paginate(10);

        // Filter user berdasarkan id pelanggan untuk opsi pilihan di form (Hanya yang belum upload)
        $users = User::role('pelanggan')
            ->where('status_verifikasi', 'belum_upload')
            ->orderBy('name')
            ->get();

        return view('livewire.menu.operasional.verifikasi-user-index', [
            'identitas' => $identitas,
            'users' => $users
        ]);
    }

    // --- FITUR ACTION ---

    public function approve($id)
    {
        abort_if(Gate::denies('update-user_identifications'), 403, 'Akses ditolak.');
        $user = User::findOrFail($id);
        $user->update([
            'status_verifikasi' => 'disetujui',
            'alasan_penolakan' => null // Kosongkan alasan jika disetujui
        ]);
        $this->dispatch('notify', message: 'Dokumen pengguna disetujui.', type: 'success');
    }

    public function reject($id)
    {
        abort_if(Gate::denies('update-user_identifications'), 403, 'Akses ditolak.');
        $user = User::findOrFail($id);
        $user->update([
            'status_verifikasi' => 'ditolak',
            // Default pesan tolak, bisa dikembangkan di fitur selanjutnya agar admin bisa input text
            'alasan_penolakan' => 'Dokumen buram atau tidak valid. Silakan unggah ulang identitas yang lebih jelas.'
        ]);
        $this->dispatch('notify', message: 'Dokumen pengguna ditolak.', type: 'error');
    }

    // --- FITUR CRUD ---

    public function create()
    {
        abort_if(Gate::denies('create-user_identifications'), 403, 'Akses ditolak.');
        
        $this->resetForm();
        $this->modalTitle = 'Upload Dokumen Identitas Baru';
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-user_identifications'), 403);
        $this->validate(); 

        $user = User::findOrFail($this->user_id);
        $user->update([
            'status_verifikasi' => $this->status_verifikasi,
            'foto_ktp' => $this->ktp_file->store('ktp', 'public'),
            'foto_sim' => $this->sim_file->store('sim', 'public'),
            'alasan_penolakan' => null
        ]);

        $this->closeModal();
        $this->dispatch('notify', message: 'Data identitas berhasil ditambahkan.', type: 'success');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('update-user_identifications'), 403, 'Akses ditolak.');

        $user = User::findOrFail($id);
        
        $this->editingId = $id;
        $this->user_id = $user->id;
        $this->status_verifikasi = $user->status_verifikasi;
        $this->existing_ktp = $user->foto_ktp;
        $this->existing_sim = $user->foto_sim;

        $this->modalTitle = 'Edit Data Dokumen';
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-user_identifications'), 403);
        $this->validate(); 

        $user = User::findOrFail($this->editingId);

        $data = [
            'status_verifikasi' => $this->status_verifikasi,
            'alasan_penolakan' => ($this->status_verifikasi === 'ditolak') ? $user->alasan_penolakan : null
        ];

        if ($this->ktp_file) {
            if ($user->foto_ktp && Storage::disk('public')->exists($user->foto_ktp)) {
                Storage::disk('public')->delete($user->foto_ktp);
            }
            $data['foto_ktp'] = $this->ktp_file->store('ktp', 'public');
        }

        if ($this->sim_file) {
            if ($user->foto_sim && Storage::disk('public')->exists($user->foto_sim)) {
                Storage::disk('public')->delete($user->foto_sim);
            }
            $data['foto_sim'] = $this->sim_file->store('sim', 'public');
        }

        $user->update($data);

        $this->closeModal();
        $this->dispatch('notify', message: 'Data identitas berhasil diperbarui.', type: 'success');
    }

    // Standar: Fungsi detail dokumen/user
    public function showDetail($id)
    {
        abort_if(Gate::denies('read-user_identifications'), 403, 'Akses ditolak.');
        $this->detailData = User::findOrFail($id);
        $this->showDetailModal = true;
    }

    public function delete($id)
    {
        abort_if(Gate::denies('delete-user_identifications'), 403, 'Akses ditolak.');

        $user = User::findOrFail($id);

        if ($user->foto_ktp && Storage::disk('public')->exists($user->foto_ktp)) {
            Storage::disk('public')->delete($user->foto_ktp);
        }
        if ($user->foto_sim && Storage::disk('public')->exists($user->foto_sim)) {
            Storage::disk('public')->delete($user->foto_sim);
        }

        // HANYA reset kolom identitas, BUKAN menghapus User sepenuhnya
        $user->update([
            'foto_ktp' => null,
            'foto_sim' => null,
            'status_verifikasi' => 'belum_upload',
            'alasan_penolakan' => null
        ]);

        $this->dispatch('notify', message: 'Data identitas berhasil dihapus & status di-reset.', type: 'success');
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->detailData = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'user_id', 'ktp_file', 'sim_file', 'status_verifikasi', 
            'editingId', 'existing_ktp', 'existing_sim'
        ]);
        $this->resetErrorBag();
    }
}