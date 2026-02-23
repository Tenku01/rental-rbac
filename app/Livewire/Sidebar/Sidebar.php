<?php

namespace App\Livewire\Sidebar;

use Livewire\Component;
use App\Models\Sopir;
use Illuminate\Support\Facades\Auth;

class Sidebar extends Component
{
    public $sopir;

    public function mount()
    {
        $this->loadSopirData();
    }

    /**
     * Memuat data sopir secara dinamis.
     * Jika Admin yang login, variabel $sopir akan tetap null.
     */
    public function loadSopirData()
    {
        if (Auth::check()) {
            $this->sopir = Sopir::where('user_id', Auth::id())->first();
        }
    }

    /**
     * Logika Toggle Status khusus Sopir yang dapat dipanggil dari Sidebar
     */
    public function toggleStatus()
    {
        if (!$this->sopir) return;

        // Proteksi: Jika sedang bekerja, status tidak bisa diubah manual menjadi tidak tersedia
        if (strtolower($this->sopir->status) === 'bekerja') {
            $this->dispatch('notify', message: 'Status terkunci saat sedang bertugas!', type: 'error');
            return;
        }

        // Toggle antara tersedia dan tidak tersedia
        $newStatus = (strtolower($this->sopir->status) === 'tersedia') ? 'tidak tersedia' : 'tersedia';
        
        $this->sopir->update(['status' => $newStatus]);
        
        $this->dispatch('notify', message: 'Status Anda berhasil diperbarui menjadi ' . ucwords($newStatus), type: 'success');
    }

    public function render()
    {
        // Pastikan path ini sesuai dengan lokasi file blade yang baru
        return view('livewire.layout.sidebar');
    }
}