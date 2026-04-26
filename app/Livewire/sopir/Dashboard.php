<?php

namespace App\Livewire\Sopir;

use Livewire\Component;
use App\Models\Sopir;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

class Dashboard extends Component
{
    public $sopir;
    public $tugasAktifCount = 0;
    public $riwayatSelesaiCount = 0;
    
    // State untuk Modal Selesaikan Tugas
    public $showCompleteModal = false;
    public $selectedTaskId = null;
    public $kondisi_mobil = '';

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $user = Auth::user();
        $this->sopir = Sopir::where('user_id', $user->id)->first();

        if ($this->sopir) {
            // Tugas Aktif: disetujui, pembayaran dp, lunas, berlangsung
            $this->tugasAktifCount = Peminjaman::where('sopir_id', $this->sopir->id)
                ->whereIn('status', ['disetujui', 'pembayaran dp', 'sudah dibayar lunas', 'berlangsung'])
                ->count();

            // Riwayat Selesai
            $this->riwayatSelesaiCount = Peminjaman::where('sopir_id', $this->sopir->id)
                ->where('status', 'selesai')
                ->count();
        }
    }

    public function toggleStatus()
    {
        if (!$this->sopir) return;

        if ($this->sopir->status === 'Bekerja') {
            $this->dispatch('notify', message: 'Dilarang mengubah status saat sedang bertugas!', type: 'error');
            return;
        }

        // Tentukan status kebalikannya
        $newStatus = ($this->sopir->status === 'Tersedia') ? 'Tidak Tersedia' : 'Tersedia';
        
        // 1. Simpan perubahan ke Database
        $this->sopir->update(['status' => $newStatus]);
        
        // 2. PERBAIKAN: Refresh data di memori Livewire agar UI langsung berubah detik itu juga!
        $this->sopir->status = $newStatus; 

        // Opsional: Kirim notifikasi sukses
        $this->dispatch('notify', message: 'Status berhasil diubah!', type: 'success');
    }

    #[Layout('layouts.sopir')] // Menggunakan layout lama Anda
    public function render()
    {
        return view('livewire.sopir.dashboard');
    }
}