<?php

namespace App\Livewire\Menu\Transaksi;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Peminjaman;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\Mobil;
use App\Models\Sopir;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PeminjamanIndex extends Component
{
    use WithPagination;
    use WithFileUploads;

    // --- Filters & Search ---
    public $filterStatus = '';
    public $search = '';
    public $activeTab = 'biasa'; // Tab default: 'biasa' atau 'pengecekan'

    // --- Modal States ---
    public $showCreateModal = false;
    public $showDetailModal = false;
    public $showPaymentModal = false; 
    public $showCheckModal = false;
    
    // --- Data Properties ---
    public $selectedPeminjaman = null;

    // --- Form Create Manual ---
    public $user_id, $mobil_id, $sopir_id;
    public $tanggal_sewa, $jam_sewa = '08:00', $tanggal_kembali;
    public $add_on_sopir = false;
    public $total_harga = 0;
    public $bayar_awal = 0; 
    public $bukti_bayar_awal;
    public $return_notice = ''; 

    // --- Form Tambah Pembayaran ---
    public $payment_amount = 0;
    public $payment_note = 'Pelunasan Cash/Transfer Manual';
    public $payment_proof;

    // --- Form Edit Status ---
    public $status_peminjaman_edit;

    // --- Form Pengecekan Mobil (Staff/Admin) ---
    public $kondisi_mobil_input = '';

    protected $paginationTheme = 'tailwind';

    public function updatedFilterStatus() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    public function updated($propertyName)
    {
        $calcFields = ['mobil_id', 'tanggal_sewa', 'tanggal_kembali', 'jam_sewa', 'add_on_sopir'];
        
        if (Str::contains($propertyName, $calcFields)) {
            $this->calculateTotal();
            $this->updateReturnNotice();
        }
    }

    // --- TAB MANAGEMENT ---
    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    private function updateReturnNotice()
    {
        if ($this->tanggal_kembali && $this->jam_sewa) {
            Carbon::setLocale('id');
            $date = Carbon::parse($this->tanggal_kembali)->translatedFormat('l, d F Y');
            $this->return_notice = "Batas waktu pengembalian armada adalah hari {$date} pukul {$this->jam_sewa}. Keterlambatan akan dikenakan denda.";
        } else {
            $this->return_notice = '';
        }
    }

    public function calculateTotal()
    {
        if ($this->mobil_id && $this->tanggal_sewa && $this->tanggal_kembali && $this->jam_sewa) {
            $mobil = Mobil::find($this->mobil_id);
            if (!$mobil) return;

            $start = Carbon::parse($this->tanggal_sewa . ' ' . $this->jam_sewa);
            $end = Carbon::parse($this->tanggal_kembali . ' ' . $this->jam_sewa);
            
            if ($end->lte($start)) {
                $this->total_harga = 0;
                return;
            }

            $lama = ceil($start->diffInHours($end) / 24);
            $lama = max($lama, 1);

            $biayaSewa = $lama * $mobil->harga;
            $biayaSopir = $this->add_on_sopir ? 150000 * $lama : 0; 

            $this->total_harga = $biayaSewa + $biayaSopir;
        }
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        // Minimal harus punya akses read-peminjaman (Dimiliki Admin & Staff)
        abort_if(Gate::denies('read-peminjaman'), 403);

        $query = Peminjaman::with(['user', 'mobil', 'paymentTransactions'])
            ->when($this->search, function($q) {
                $q->where('id', 'like', '%'.$this->search.'%')
                  ->orWhereHas('user', fn($sq) => $sq->where('name', 'like', '%'.$this->search.'%'))
                  ->orWhereHas('mobil', fn($sq) => $sq->where('plat_nomor', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderBy('created_at', 'desc');

        // LOGIKA PEMISAHAN TAB
        if ($this->activeTab === 'pengecekan') {
            // Tab Tugas Pengecekan: Hanya data yg butuh di-inspeksi (Sudah bayar/DP tapi kondisi belum diisi)
            $query->whereIn('status', ['pembayaran dp', 'sudah dibayar lunas'])
                  ->whereNull('kondisi_mobil');
        } else {
            // Tab Data Biasa: Exclude data yg sedang antre dicek
            $query->where(function($q) {
                $q->whereNotIn('status', ['pembayaran dp', 'sudah dibayar lunas'])
                  ->orWhereNotNull('kondisi_mobil');
            });
        }

        $sopirsQuery = Sopir::whereIn('status', ['tersedia', 'bekerja']);
        
        if ($this->tanggal_sewa && $this->tanggal_kembali) {
            $start = Carbon::parse($this->tanggal_sewa)->format('Y-m-d');
            $end = Carbon::parse($this->tanggal_kembali)->format('Y-m-d');

            $sopirsQuery->whereDoesntHave('peminjaman', function($q) use ($start, $end) {
                $q->whereIn('status', ['menunggu pembayaran', 'pembayaran dp', 'sudah dibayar lunas', 'berlangsung'])
                  ->where(function($sq) use ($start, $end) {
                      $sq->where('tanggal_sewa', '<=', $end)
                         ->where('tanggal_kembali', '>=', $start);
                  });
            });
        }

        return view('livewire.menu.transaksi.peminjaman-index', [
            'peminjaman' => $query->paginate(10),
            'users_list' => User::role('pelanggan')->orderBy('name')->get(),
            'mobils_list' => Mobil::where('status', 'tersedia')->orderBy('merek')->get(),
            'sopirs_list' => ($this->tanggal_sewa && $this->tanggal_kembali) ? $sopirsQuery->orderBy('nama')->get() : collect([]),
        ]);
    }

    // =========================================================================
    // CRUD: CREATE & STORE (Hanya Admin)
    // =========================================================================
    
    public function openCreateModal() { $this->resetForm(); $this->showCreateModal = true; }

    public function storeManual()
    {
        abort_if(Gate::denies('create-peminjaman'), 403); // RBAC: Protect function

        $this->validate([
            'user_id' => 'required|exists:users,id',
            'mobil_id' => 'required|exists:mobils,id',
            'tanggal_sewa' => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_sewa',
            'jam_sewa' => 'required',
            'total_harga' => 'required|numeric|min:0',
            'bayar_awal' => 'nullable|numeric|min:0|lte:total_harga',
        ]);

        DB::beginTransaction();
        try {
            $buktiPath = $this->bukti_bayar_awal ? $this->bukti_bayar_awal->store('bukti_transaksi', 'public') : null;

            $bayar = $this->bayar_awal ?? 0;
            $sisa = $this->total_harga - $bayar;
            
            $status = 'menunggu pembayaran';
            $tipe_bayar = 'dp'; 

            if ($bayar >= $this->total_harga) {
                $status = 'sudah dibayar lunas';
                $tipe_bayar = 'lunas';
            } elseif ($bayar > 0) {
                $status = 'pembayaran dp';
                $tipe_bayar = 'dp';
            }

            $peminjaman = Peminjaman::create([
                'user_id' => $this->user_id,
                'mobil_id' => $this->mobil_id,
                'sopir_id' => $this->add_on_sopir ? $this->sopir_id : null,
                'tanggal_sewa' => $this->tanggal_sewa,
                'jam_sewa' => $this->jam_sewa,
                'tanggal_kembali' => $this->tanggal_kembali,
                'add_on_sopir' => $this->add_on_sopir ? 1 : 0,
                'total_harga' => $this->total_harga,
                'dp_dibayarkan' => ($tipe_bayar == 'dp') ? $bayar : 0,
                'total_dibayarkan' => $bayar,
                'sisa_bayar' => $sisa,
                'status' => $status,
                'metode_pembayaran' => 'manual',
                'tipe_pembayaran' => $tipe_bayar,
                'bukti_transaksi' => $buktiPath,
            ]);

            if ($bayar > 0) {
                PaymentTransaction::create([
                    'peminjaman_id' => $peminjaman->id,
                    'midtrans_transaction_id' => 'MANUAL-' . strtoupper(Str::random(10)),
                    'status' => 'settlement',
                    'amount' => $bayar,
                    'tipe_transaksi' => $tipe_bayar,
                    'midtrans_response' => json_encode([
                        'channel' => 'manual_cash',
                        'admin_id' => Auth::id(),
                        'note' => 'Pembayaran Offline Admin'
                    ]),
                ]);
            }

            DB::commit();
            $this->closeModal();
            $this->dispatch('notify', message: 'Booking manual berhasil dibuat!', type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Error: ' . $e->getMessage(), type: 'error');
        }
    }

    // =========================================================================
    // CRUD: MANAGE PAYMENT (Hanya Admin)
    // =========================================================================

    public function openPaymentModal($id)
    {
        $this->selectedPeminjaman = Peminjaman::with(['paymentTransactions'])->findOrFail($id);
        $this->payment_amount = $this->selectedPeminjaman->sisa_bayar;
        $this->showPaymentModal = true;
    }

    public function storePayment()
    {
        abort_if(Gate::denies('update-peminjaman'), 403);

        $this->validate([
            'payment_amount' => 'required|numeric|min:1|lte:' . $this->selectedPeminjaman->sisa_bayar,
        ]);

        DB::beginTransaction();
        try {
            PaymentTransaction::create([
                'peminjaman_id' => $this->selectedPeminjaman->id,
                'midtrans_transaction_id' => 'MANUAL-PAY-' . strtoupper(Str::random(8)),
                'status' => 'settlement',
                'amount' => $this->payment_amount,
                'tipe_transaksi' => 'pelunasan',
                'midtrans_response' => json_encode(['admin_id' => Auth::id()]),
            ]);

            $total_paid = $this->selectedPeminjaman->paymentTransactions()->where('status', 'settlement')->sum('amount');
            $sisa = $this->selectedPeminjaman->total_harga - $total_paid;
            
            $this->selectedPeminjaman->update([
                'total_dibayarkan' => $total_paid,
                'sisa_bayar' => $sisa,
                'status' => ($sisa <= 0) ? 'sudah dibayar lunas' : 'pembayaran dp',
                'tipe_pembayaran' => ($sisa <= 0) ? 'lunas' : 'dp'
            ]);

            DB::commit();
            $this->showPaymentModal = false;
            $this->dispatch('notify', message: 'Pembayaran tambahan berhasil dicatat!', type: 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Gagal: ' . $e->getMessage(), type: 'error');
        }
    }

    // =========================================================================
    // CRUD: PENGECEKAN KENDARAAN (Admin & Staff)
    // =========================================================================

    public function openCheckModal($id)
    {
        $this->selectedPeminjaman = Peminjaman::findOrFail($id);
        $this->kondisi_mobil_input = $this->selectedPeminjaman->kondisi_mobil ?? '';
        $this->showCheckModal = true;
    }

    public function storeCheck()
    {
        // RBAC Khusus: Izinkan jika User memiliki Gate 'update-peminjaman' (Admin) 
        // ATAU memiliki Gate 'create-vehicle_inspections' (Staff)
        if (Gate::denies('update-peminjaman') && Gate::denies('create-vehicle_inspections')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melakukan inspeksi kendaraan.');
        }

        $this->validate([
            'kondisi_mobil_input' => 'required|string|min:5',
        ]);

        DB::beginTransaction();
        try {
            $this->selectedPeminjaman->update([
                'kondisi_mobil' => $this->kondisi_mobil_input,
                'status' => 'berlangsung'
            ]);

            // Sync Status Mobil
            $this->selectedPeminjaman->mobil()->update(['status' => 'disewa']);

            DB::commit();
            $this->closeModal();
            $this->dispatch('notify', message: 'Inspeksi Selesai! Kendaraan telah diserahkan & sedang berjalan.', type: 'success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Gagal: ' . $e->getMessage(), type: 'error');
        }
    }

    // =========================================================================
    // CRUD: MANAGE STATUS OPERASIONAL (Hanya Admin)
    // =========================================================================

    public function showDetail($id)
    {
        $this->selectedPeminjaman = Peminjaman::with(['user', 'mobil', 'paymentTransactions'])->findOrFail($id);
        $this->status_peminjaman_edit = $this->selectedPeminjaman->status;
        $this->showDetailModal = true;
    }

    public function updateStatus()
    {
        abort_if(Gate::denies('update-peminjaman'), 403);

        $this->selectedPeminjaman->update(['status' => $this->status_peminjaman_edit]);

        if ($this->status_peminjaman_edit == 'berlangsung') {
            $this->selectedPeminjaman->mobil()->update(['status' => 'disewa']);
        } elseif (in_array($this->status_peminjaman_edit, ['selesai', 'dibatalkan'])) {
            $this->selectedPeminjaman->mobil()->update(['status' => 'tersedia']);
        }

        $this->showDetailModal = false;
        $this->dispatch('notify', message: 'Status operasional diperbarui.', type: 'success');
    }

    public function delete($id)
    {
        abort_if(Gate::denies('delete-peminjaman'), 403);
        $p = Peminjaman::findOrFail($id);
        if ($p->status == 'berlangsung') {
            $this->dispatch('notify', message: 'Tidak bisa menghapus transaksi berjalan.', type: 'error');
            return;
        }
        $p->delete();
        $this->dispatch('notify', message: 'Data dihapus.', type: 'success');
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showDetailModal = false;
        $this->showPaymentModal = false;
        $this->showCheckModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'user_id', 'mobil_id', 'sopir_id', 'tanggal_sewa', 'tanggal_kembali', 'jam_sewa',
            'add_on_sopir', 'total_harga', 'bayar_awal', 'return_notice', 'selectedPeminjaman',
            'kondisi_mobil_input'
        ]);
        $this->resetErrorBag();
    }
}