<?php

namespace App\Livewire\Sopir;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Peminjaman;
use App\Models\LogbookSopir;
use App\Models\Sopir;
use App\Models\Pesan;
use Illuminate\Support\Facades\Auth;

class RiwayatPenugasan extends Component
{
    // State Navigasi UX
    public $viewMode = 'index'; // 'index' atau 'detail'
    
    // State Data
    public $sopir;
    public $selectedTask;
    public $logHistory = [];
    public $chatHistory = [];

    public function mount()
    {
        $this->sopir = Sopir::where('user_id', Auth::id())->first();
    }

    #[Layout('layouts.sopir')]
    public function render()
    {
        $riwayatTugas = collect();

        if ($this->sopir && $this->viewMode === 'index') {
            $riwayatTugas = Peminjaman::with(['mobil', 'user', 'pengembalian'])
                ->where('sopir_id', $this->sopir->id)
                ->where('status', 'selesai') // Hanya ambil yang statusnya sudah selesai
                ->orderBy('created_at', 'desc') 
                ->get();
        }

        return view('livewire.sopir.riwayat-penugasan', [
            'riwayatTugas' => $riwayatTugas
        ]);
    }

    public function viewDetail($peminjamanId)
    {
        $this->selectedTask = Peminjaman::with(['mobil', 'user', 'pengembalian'])->findOrFail($peminjamanId);
        
        // Load history logbook
        $this->logHistory = LogbookSopir::where('peminjaman_id', $this->selectedTask->id)
            ->orderBy('waktu_log', 'asc')
            ->get();
            
        // Load riwayat chat
        $this->chatHistory = Pesan::with('pengirim')
            ->where('peminjaman_id', $this->selectedTask->id)
            ->orderBy('created_at', 'asc')
            ->get();
        
        $this->viewMode = 'detail';
    }

    public function backToIndex()
    {
        $this->viewMode = 'index';
        $this->selectedTask = null;
        $this->logHistory = [];
        $this->chatHistory = [];
    }
}