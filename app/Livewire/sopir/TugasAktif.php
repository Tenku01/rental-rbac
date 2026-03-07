<?php

namespace App\Livewire\Sopir;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Peminjaman;
use App\Models\DriverLogbook; 
use App\Models\Sopir;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; 
use Carbon\Carbon;

class TugasAktif extends Component
{
    use WithFileUploads;

    // State Navigasi UX
    public $viewMode = 'index'; // 'index' atau 'form'
    
    // State Data
    public $sopir;
    public $selectedTask;
    public $logHistory = [];

    // State Form Logbook
    public $status_log = '';
    public $deskripsi_aktivitas = '';
    public $foto_bukti;

    public function mount()
    {
        $this->sopir = Sopir::where('user_id', Auth::id())->first();
    }

    #[Layout('layouts.sopir')]
    public function render()
    {
        // RBAC: Read Permission
        abort_if(Gate::denies('read-driver_logbooks'), 403, 'Akses ditolak.');

        $tasks = collect();
        $stats = [
            'total' => 0,
            'sudah_hari_ini' => 0,
            'belum_hari_ini' => 0,
        ];

        if ($this->sopir && $this->viewMode === 'index') {
            $tasks = Peminjaman::with(['mobil', 'user.pelanggan', 'logbooks' => function($q) {
                // PERBAIKAN: Gunakan waktu_log, bukan created_at
                $q->whereDate('waktu_log', Carbon::today());
            }])
            ->where('sopir_id', $this->sopir->id)
            ->whereIn('status', ['disetujui', 'pembayaran dp', 'sudah dibayar lunas', 'berlangsung'])
            // created_at di bawah ini milik tabel Peminjaman (jadi tidak apa-apa)
            ->orderBy('created_at', 'desc') 
            ->get();

            $stats['total'] = $tasks->count();
            $stats['sudah_hari_ini'] = $tasks->filter(fn($task) => $task->logbooks->count() > 0)->count();
            $stats['belum_hari_ini'] = $stats['total'] - $stats['sudah_hari_ini'];
        }

        return view('livewire.sopir.tugas-aktif', [
            'tasks' => $tasks,
            'stats' => $stats
        ]);
    }

    public function openLogbookForm($peminjamanId)
    {
        // RBAC: Read Permission
        abort_if(Gate::denies('read-driver_logbooks'), 403, 'Akses ditolak.');

        $this->selectedTask = Peminjaman::with(['mobil', 'user.pelanggan'])->findOrFail($peminjamanId);
        $this->loadLogHistory();
        
        $this->resetForm();
        $this->viewMode = 'form';
    }

    public function backToIndex()
    {
        $this->viewMode = 'index';
        $this->selectedTask = null;
        $this->logHistory = [];
    }

    public function loadLogHistory()
    {
        if ($this->selectedTask) {
            $this->logHistory = DriverLogbook::where('peminjaman_id', $this->selectedTask->id)
                // PERBAIKAN: Gunakan waktu_log, bukan created_at
                ->orderBy('waktu_log', 'desc')
                ->get();
        }
    }

    public function saveLog()
    {
        // RBAC: Create/Update Permission
        abort_if(Gate::denies('create-driver_logbooks'), 403, 'Akses ditolak.');

        $this->validate([
            'status_log' => 'required|string',
            'deskripsi_aktivitas' => 'required|string|min:10|max:500',
            'foto_bukti' => 'nullable|image|max:2048', 
        ]);

        $path = null;
        if ($this->foto_bukti) {
            $path = $this->foto_bukti->store('logbook_photos', 'public');
        }

        DriverLogbook::create([
            'peminjaman_id' => $this->selectedTask->id,
            'status_log' => $this->status_log,
            'deskripsi_aktivitas' => $this->deskripsi_aktivitas,
            'foto_bukti' => $path,
            'tanggal_aktivitas' => Carbon::today(), // Sesuaikan agar tanggal terisi
            'waktu_log' => now(), 
        ]);

        if ($this->status_log === 'selesai_peminjaman') {
            $this->selectedTask->update(['status' => 'selesai']);
            
            if ($this->sopir) {
                $this->sopir->update(['status' => 'tersedia']);
            }

            $this->dispatch('notify', message: 'Tugas diselesaikan! Status Anda kembali Tersedia.', type: 'success');
            $this->backToIndex(); 
            return;
        }

        $this->dispatch('notify', message: 'Logbook berhasil dicatat!', type: 'success');
        $this->loadLogHistory(); 
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->status_log = '';
        $this->deskripsi_aktivitas = '';
        $this->foto_bukti = null;
    }
}