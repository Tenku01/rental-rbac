<?php

namespace App\Livewire\Menu\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads; 
use Livewire\Attributes\Layout;
use App\Models\UserIdentification;
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

    // Modal State
    public $showModal = false;
    public $modalTitle = '';
    public $isEditMode = false;
    public $editingId = null;

    // Form Fields
    public $user_id;
    public $ktp_file;
    public $sim_file;
    public $status_approval = 'menunggu';
    
    // Preview file lama saat edit
    public $existing_ktp;
    public $existing_sim;

    protected $paginationTheme = 'tailwind';

    // Reset pagination saat filter berubah
    public function updatedFilterStatus() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    #[Layout('layouts.admin')]
    public function render()
    {
        abort_if(Gate::denies('read-verification'), 403, 'Anda tidak memiliki akses melihat data verifikasi.');

        $identitas = UserIdentification::with('user')
            ->when($this->search, function($q) {
                $q->whereHas('user', function($sq) {
                    $sq->where('name', 'like', '%' . $this->search . '%')
                       ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function($q) {
                return $q->where('status_approval', $this->filterStatus);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // PERUBAHAN: Filter hanya ambil user dengan role 'pelanggan'
        $users = User::role('pelanggan')->orderBy('name')->get();

        return view('livewire.menu.operasional.verifikasi-user-index', [
            'identitas' => $identitas,
            'users' => $users
        ]);
    }

    // ... (Sisa fungsi approve, reject, create, store, edit, update, delete tetap sama) ...
    // Saya sertakan kembali fungsi CRUD agar file tetap lengkap dan runnable

    public function approve($id)
    {
        abort_if(Gate::denies('update-verification'), 403, 'Akses ditolak.');

        $doc = UserIdentification::findOrFail($id);
        $doc->update([
            'status_approval' => 'disetujui',
            'verified_at' => now(),
            'verified_by' => Auth::id()
        ]);

        session()->flash('message', 'Dokumen identitas pengguna ' . ($doc->user->name ?? 'User') . ' telah DISETUJUI.');
    }

    public function reject($id)
    {
        abort_if(Gate::denies('update-verification'), 403, 'Akses ditolak.');

        $doc = UserIdentification::findOrFail($id);
        $doc->update([
            'status_approval' => 'ditolak',
            'verified_at' => now(),
            'verified_by' => Auth::id()
        ]);

        session()->flash('error', 'Dokumen identitas pengguna ' . ($doc->user->name ?? 'User') . ' telah DITOLAK.');
    }

    public function create()
    {
        abort_if(Gate::denies('create-verification'), 403, 'Akses ditolak.');
        
        $this->resetForm();
        $this->modalTitle = 'Upload Dokumen Identitas Baru';
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-verification'), 403);

        $this->validate([
            'user_id' => 'required|exists:users,id|unique:user_identifications,user_id',
            'ktp_file' => 'required|image|max:2048',
            'sim_file' => 'nullable|image|max:2048',
            'status_approval' => 'required|in:menunggu,disetujui,ditolak',
        ]);

        $data = [
            'user_id' => $this->user_id,
            'status_approval' => $this->status_approval,
            'ktp' => $this->ktp_file->store('ktp', 'public'),
        ];

        if ($this->sim_file) {
            $data['sim'] = $this->sim_file->store('sim', 'public');
        }

        if ($this->status_approval !== 'menunggu') {
            $data['verified_at'] = now();
            $data['verified_by'] = Auth::id();
        }

        UserIdentification::create($data);

        $this->closeModal();
        $this->dispatch('notify', message: 'Data identitas berhasil ditambahkan.', type: 'success');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('update-verification'), 403, 'Akses ditolak.');

        $doc = UserIdentification::findOrFail($id);
        
        $this->editingId = $id;
        $this->user_id = $doc->user_id;
        $this->status_approval = $doc->status_approval;
        $this->existing_ktp = $doc->ktp;
        $this->existing_sim = $doc->sim;

        $this->modalTitle = 'Edit Data Dokumen';
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-verification'), 403);

        $this->validate([
            'user_id' => 'required|exists:users,id|unique:user_identifications,user_id,' . $this->editingId,
            'ktp_file' => 'nullable|image|max:2048',
            'sim_file' => 'nullable|image|max:2048',
            'status_approval' => 'required|in:menunggu,disetujui,ditolak',
        ]);

        $doc = UserIdentification::findOrFail($this->editingId);

        $data = [
            'user_id' => $this->user_id,
            'status_approval' => $this->status_approval,
        ];

        if ($this->ktp_file) {
            if ($doc->ktp && Storage::disk('public')->exists($doc->ktp)) {
                Storage::disk('public')->delete($doc->ktp);
            }
            $data['ktp'] = $this->ktp_file->store('ktp', 'public');
        }

        if ($this->sim_file) {
            if ($doc->sim && Storage::disk('public')->exists($doc->sim)) {
                Storage::disk('public')->delete($doc->sim);
            }
            $data['sim'] = $this->sim_file->store('sim', 'public');
        }

        if ($this->status_approval !== 'menunggu' && $doc->status_approval === 'menunggu') {
            $data['verified_at'] = now();
            $data['verified_by'] = Auth::id();
        }

        $doc->update($data);

        $this->closeModal();
        $this->dispatch('notify', message: 'Data identitas berhasil diperbarui.', type: 'success');
    }

    public function delete($id)
    {
        abort_if(Gate::denies('delete-verification'), 403, 'Akses ditolak.');

        $doc = UserIdentification::findOrFail($id);

        if ($doc->ktp && Storage::disk('public')->exists($doc->ktp)) {
            Storage::disk('public')->delete($doc->ktp);
        }
        if ($doc->sim && Storage::disk('public')->exists($doc->sim)) {
            Storage::disk('public')->delete($doc->sim);
        }

        $doc->delete();

        $this->dispatch('notify', message: 'Data identitas berhasil dihapus.', type: 'success');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'user_id', 'ktp_file', 'sim_file', 'status_approval', 
            'editingId', 'existing_ktp', 'existing_sim'
        ]);
        $this->resetErrorBag();
    }
}