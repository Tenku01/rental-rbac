<?php

namespace App\Livewire\Menu\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\VehicleInspection;
use App\Models\Pengembalian;
use App\Models\VehicleDamageReport;
use App\Models\Fine; // Model untuk tabel fines
use App\Models\Mobil;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VehicleInspectionIndex extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $filterCondition = '';

    public $showModal = false;
    public $showDetailModal = false;
    public $selectedInspection = null;

    // Form Fields
    public $pengembalian_id;
    public $inspection_date;
    public $condition = 'Baik Sempurna';
    public $notes;
    public $photo;

    // Data UI & Perhitungan
    public $infoPenyewa, $infoMobil, $jadwalKembali, $hargaPerHari;
    public $jamTerlambat = 0;
    public $lateFine = 0;

    // Logic Kerusakan
    public $isDamaged = false;
    public $damage_description;
    public $damage_cost = 0;

    protected $paginationTheme = 'tailwind';

    public function updatedSearch() { $this->resetPage(); }

    #[Layout('layouts.admin')]
    public function render()
    {
        abort_if(Gate::denies('read-vehicle_inspections'), 403);

        $pendingReturns = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
            ->where('status', 'menunggu pengecekan')
            ->orderBy('tanggal_pengembalian', 'asc')
            ->get();

        $inspections = VehicleInspection::with(['mobil', 'staff', 'pengembalian.peminjaman.user'])
            ->when($this->search, function($q) {
                $q->whereHas('mobil', fn($sq) => $sq->where('plat_nomor', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterCondition, fn($q) => $q->where('condition', $this->filterCondition))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.menu.operasional.vehicle-inspection-index', [
            'pendingReturns' => $pendingReturns,
            'inspections' => $inspections,
            'active_returns' => Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
                                ->where('status', 'menunggu pengecekan')
                                ->get()
        ]);
    }

    public function createInspection($returnId)
    {
        abort_if(Gate::denies('create-vehicle_inspections'), 403);
        $this->resetForm();
        
        $return = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])->findOrFail($returnId);
        
        $this->pengembalian_id = $returnId;
        $this->infoPenyewa = $return->peminjaman->user->name;
        $this->infoMobil = $return->peminjaman->mobil->merek . ' [' . $return->peminjaman->mobil->plat_nomor . ']';
        $this->hargaPerHari = $return->peminjaman->mobil->harga;
        
        $waktuSeharusnya = Carbon::parse($return->peminjaman->tanggal_kembali . ' ' . $return->peminjaman->jam_sewa);
        $waktuSekarang = now();

        if ($waktuSekarang->gt($waktuSeharusnya)) {
            $this->jamTerlambat = ceil($waktuSeharusnya->diffInHours($waktuSekarang));
            // Rumus: (10% Harga Harian) x Jam Terlambat
            $this->lateFine = ($this->hargaPerHari * 0.1) * $this->jamTerlambat;
        }

        $this->inspection_date = now()->format('Y-m-d H:i');
        $this->showModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-vehicle_inspections'), 403);

        $this->validate([
            'pengembalian_id' => 'required',
            'condition' => 'required',
            'notes' => 'required|min:5',
            'photo' => 'nullable|image|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $return = Pengembalian::with('peminjaman')->findOrFail($this->pengembalian_id);
            $path = $this->photo ? $this->photo->store('inspections', 'public') : null;

            // 1. Simpan Log Inspeksi
            VehicleInspection::create([
                'pengembalian_id' => $this->pengembalian_id,
                'mobil_id' => $return->peminjaman->mobil_id,
                'staff_id' => Auth::id(),
                'inspection_date' => $this->inspection_date,
                'condition' => $this->condition,
                'notes' => $this->notes,
                'photo' => $path
            ]);

            $totalDendaKerusakan = 0;

            // 2. Simpan Laporan Kerusakan (jika ada)
            if ($this->isDamaged && $this->damage_cost > 0) {
                $platNo = str_replace(' ', '', $return->peminjaman->mobil_id);
                $kodeLaporan = 'DMG-' . strtoupper($platNo) . '-' . $return->id . '-' . date('His');

                VehicleDamageReport::create([
                    'kode_laporan' => $kodeLaporan,
                    'mobil_id' => $return->peminjaman->mobil_id,
                    'pengembalian_kode' => $return->kode_pengembalian,
                    'damage_description' => $this->damage_description,
                    'damage_cost' => $this->damage_cost,
                ]);

                $totalDendaKerusakan = $this->damage_cost;
            }

            // 3. Simpan ke Tabel Fines (Denda Terpadu)
            if ($this->lateFine > 0 || $totalDendaKerusakan > 0) {
                Fine::create([
                    'peminjaman_id' => $return->peminjaman_id,
                    'denda_keterlambatan' => $this->lateFine,
                    'denda_kerusakan' => $totalDendaKerusakan,
                    'total_denda' => $this->lateFine + $totalDendaKerusakan,
                    'status' => 'belum dibayar',
                    'tanggal_terdeteksi' => now()->toDateString(),
                    'keterangan' => "Denda untuk pengembalian {$return->kode_pengembalian}. (Kerusakan: Rp" . number_format($totalDendaKerusakan) . ", Terlambat: {$this->jamTerlambat} Jam)"
                ]);

                // Update denda di tabel pengembalian sebagai rangkuman
                $return->update(['denda' => $this->lateFine + $totalDendaKerusakan]);
            }

            // 4. Update Status Pengembalian & Mobil
            $return->update(['status' => 'sudah dicek']);
            
            $mobilStatus = ($this->condition === 'Baik Sempurna') ? 'tersedia' : 'pemeliharaan';
            Mobil::where('id', $return->peminjaman->mobil_id)->update(['status' => $mobilStatus]);

            DB::commit();
            $this->closeModal();
            $this->dispatch('notify', message: 'Finalisasi pengecekan berhasil dan denda telah dicatat.', type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Gagal memproses data: ' . $e->getMessage(), type: 'error');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showDetailModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'pengembalian_id', 'condition', 'notes', 'photo', 'isDamaged', 
            'damage_description', 'damage_cost', 'jamTerlambat', 'lateFine'
        ]);
        $this->resetErrorBag();
    }
}