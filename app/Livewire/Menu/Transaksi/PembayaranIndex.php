<?php

namespace App\Livewire\Menu\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Gate;

class PembayaranIndex extends Component
{
    use WithPagination;

    // --- Filters & Search ---
    public $search = '';
    public $filterStatus = ''; // settlement, pending, expire, deny, refund
    
    // --- Modal Detail ---
    public $showDetailModal = false;
    public $selectedPayment = null;
    public $rawJson = ''; // Untuk menampilkan respon mentah Midtrans

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    #[Layout('layouts.admin')]
    public function render()
    {
        // RBAC: Hanya user dengan izin 'read-payment' yang bisa melihat
        abort_if(Gate::denies('read-payment'), 403, 'Anda tidak memiliki akses melihat data keuangan.');

        $payments = PaymentTransaction::with(['peminjaman.user', 'peminjaman.mobil'])
            ->when($this->search, function($q) {
                $q->where('midtrans_transaction_id', 'like', '%'.$this->search.'%')
                  ->orWhere('id', 'like', '%'.$this->search.'%')
                  ->orWhereHas('peminjaman.user', fn($sq) => $sq->where('name', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.menu.transaksi.pembayaran-index', [
            'payments' => $payments
        ]);
    }

    // --- Action: Show Detail ---
    public function showDetail($id)
    {
        // Membutuhkan hak akses 'read-payment' (atau 'update-payment' jika ingin lebih ketat)
        abort_if(Gate::denies('read-payment'), 403);

        $this->selectedPayment = PaymentTransaction::with(['peminjaman.user', 'peminjaman.mobil'])->findOrFail($id);
        
        // Format JSON agar mudah dibaca di modal
        $this->rawJson = json_encode(json_decode($this->selectedPayment->midtrans_response), JSON_PRETTY_PRINT);
        
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedPayment = null;
        $this->rawJson = '';
    }
}