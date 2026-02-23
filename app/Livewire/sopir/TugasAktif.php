<?php

namespace App\Livewire\Sopir;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Peminjaman;
use App\Models\DriverLogbook; // Pastikan model ini sesuai dengan nama model logbook Anda
use App\Models\Sopir;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // Import Gate untuk RBAC Spatie
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

    #[Layout('layouts.sopir')] // Tetap gunakan layout yang sudah ada sidebar barunya
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
            // Ambil tugas yang sedang aktif
            $tasks = Peminjaman::with(['mobil', 'user.pelanggan', 'logbooks' => function($q) {
                // Ambil logbook hari ini untuk dicek statusnya
                $q->whereDate('created_at', Carbon::today());
            }])
            ->where('sopir_id', $this->sopir->id)
            ->whereIn('status', ['disetujui', 'pembayaran dp', 'sudah dibayar lunas', 'berlangsung'])
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

    /**
     * Berpindah ke Mode Form Logbook
     */
    public function openLogbookForm($peminjamanId)
    {
        // RBAC: Read Permission (Melihat detail form logbook)
        abort_if(Gate::denies('read-driver_logbooks'), 403, 'Akses ditolak.');

        $this->selectedTask = Peminjaman::with(['mobil', 'user.pelanggan'])->findOrFail($peminjamanId);
        $this->loadLogHistory();
        
        $this->resetForm();
        $this->viewMode = 'form';
    }

    /**
     * Kembali ke Daftar Tugas
     */
    public function backToIndex()
    {
        $this->viewMode = 'index';
        $this->selectedTask = null;
        $this->logHistory = [];
    }

    /**
     * Memuat riwayat log khusus tugas yang dipilih
     */
    public function loadLogHistory()
    {
        if ($this->selectedTask) {
            $this->logHistory = DriverLogbook::where('peminjaman_id', $this->selectedTask->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }
    }

    /**
     * Menyimpan Data Logbook
     */
    public function saveLog()
    {
        // RBAC: Create/Update Permission (Membuat catatan logbook)
        abort_if(Gate::denies('create-driver_logbooks'), 403, 'Akses ditolak.');

        $this->validate([
            'status_log' => 'required|string',
            'deskripsi_aktivitas' => 'required|string|min:10|max:500',
            'foto_bukti' => 'nullable|image|max:2048', // max 2MB
        ]);

        $path = null;
        if ($this->foto_bukti) {
            $path = $this->foto_bukti->store('logbook_photos', 'public');
        }

        // Simpan logbook
        DriverLogbook::create([
            'peminjaman_id' => $this->selectedTask->id,
            'status_log' => $this->status_log,
            'deskripsi_aktivitas' => $this->deskripsi_aktivitas,
            'foto_bukti' => $path,
            // Sesuaikan nama field datetime Anda, default biasanya created_at. Jika pakai waktu_log:
            'waktu_log' => now(), 
        ]);

        // LOGIKA PENYELESAIAN TUGAS
        if ($this->status_log === 'selesai_peminjaman') {
            // Ubah status tugas
            $this->selectedTask->update(['status' => 'selesai']);
            
            // Bebaskan Sopir
            if ($this->sopir) {
                $this->sopir->update(['status' => 'tersedia']);
            }

            $this->dispatch('notify', message: 'Tugas diselesaikan! Status Anda kembali Tersedia.', type: 'success');
            $this->backToIndex(); // Kembali ke depan karena tugas hilang dari daftar aktif
            return;
        }

        $this->dispatch('notify', message: 'Logbook berhasil dicatat!', type: 'success');
        $this->loadLogHistory(); // Refresh riwayat di bawah form
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->status_log = '';
        $this->deskripsi_aktivitas = '';
        $this->foto_bukti = null;
    }

    
}