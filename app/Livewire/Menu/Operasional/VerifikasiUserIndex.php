<?php

namespace App\Livewire\Menu\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout; // Import Layout Attribute
use App\Models\UserIdentification;

class VerifikasiUserIndex extends Component
{
    use WithPagination;

    // Default tampilkan yang 'menunggu' agar Admin fokus ke tugas pending
    public $filterStatus = 'menunggu'; 

    protected $paginationTheme = 'tailwind';

    #[Layout('layouts.admin')] // Menggunakan Attribute
    public function render()
    {
        $identitas = UserIdentification::with('user')
            ->when($this->filterStatus, function($q) {
                return $q->where('status_approval', $this->filterStatus);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.verifikasi-user-index', [
            'identitas' => $identitas
        ]);
    }

    // Action: Setujui Dokumen
    public function approve($id)
    {
        $doc = UserIdentification::findOrFail($id);
        
        $doc->update([
            'status_approval' => 'disetujui'
        ]);

        session()->flash('message', 'Dokumen identitas pengguna ' . $doc->user->name . ' telah DISETUJUI.');
    }

    // Action: Tolak Dokumen
    public function reject($id)
    {
        $doc = UserIdentification::findOrFail($id);
        
        $doc->update([
            'status_approval' => 'ditolak'
        ]);

        session()->flash('warning', 'Dokumen identitas pengguna ' . $doc->user->name . ' telah DITOLAK.');
    }
}