<?php

namespace App\Livewire\Menu\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\LaporanKerusakanMobil; // 🔹 Diperbarui dari VehicleDamageReport
use App\Models\Mobil;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LaporanKerusakanIndex extends Component
{
    use WithPagination;

    // --- Filters & Search ---
    public $search = '';

    // --- Modal States ---
    public $showModal = false;
    public $showDetailModal = false;
    public $isEditMode = false;
    public $selectedReport = null;

    // --- Form Fields ---
    public $editingKode = null; 
    public $mobil_id = '';
    public $pengembalian_kode = '';
    public $deskripsi_kerusakan = ''; // 🔹 Diperbarui dari damage_description
    public $biaya_kerusakan = 0; // 🔹 Diperbarui dari damage_cost

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        // Permission tetap mengacu ke Spatie bawaan
        if (Gate::denies('read-vehicle_damage_reports')) {
            return redirect()->route('unauthorized');
        }
    }

    public function updatedSearch() { $this->resetPage(); }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    protected function rules()
    {
        $rules = [
            'deskripsi_kerusakan' => 'required|string|min:10', // 🔹 Diperbarui
            'biaya_kerusakan' => 'required|numeric|min:0', // 🔹 Diperbarui
        ];

        if (!$this->isEditMode) {
            $rules['mobil_id'] = 'required|exists:mobils,id';
            $rules['pengembalian_kode'] = 'nullable|string|max:50';
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            'mobil_id.required' => 'Armada mobil wajib dipilih.',
            'mobil_id.exists' => 'Data mobil tidak ditemukan di sistem.',
            'deskripsi_kerusakan.required' => 'Deskripsi rincian kerusakan wajib diisi.', // 🔹 Diperbarui
            'deskripsi_kerusakan.string' => 'Format deskripsi tidak valid.', // 🔹 Diperbarui
            'deskripsi_kerusakan.min' => 'Deskripsi terlalu singkat (minimal 10 karakter).', // 🔹 Diperbarui
            'biaya_kerusakan.required' => 'Estimasi biaya atau ganti rugi wajib diisi.', // 🔹 Diperbarui
            'biaya_kerusakan.numeric' => 'Biaya ganti rugi harus berupa angka (tanpa titik/koma).', // 🔹 Diperbarui
            'biaya_kerusakan.min' => 'Biaya ganti rugi tidak boleh bernilai negatif.', // 🔹 Diperbarui
        ];
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $reports = LaporanKerusakanMobil::with(['mobil', 'pengembalian']) // 🔹 Diperbarui
            ->when($this->search, function($q) {
                $q->where('kode_laporan', 'like', '%'.$this->search.'%')
                  ->orWhere('mobil_id', 'like', '%'.$this->search.'%')
                  ->orWhere('pengembalian_kode', 'like', '%'.$this->search.'%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Mengambil data mobil untuk dropdown saat Create
        $mobils = Mobil::orderBy('merek', 'asc')->get();

        // 🔹 NAMA VIEW DIUBAH (Jangan lupa rename file Blade-nya jadi laporan-kerusakan-index.blade.php)
        return view('livewire.menu.operasional.laporan-kerusakan-index', [
            'reports' => $reports,
            'mobils' => $mobils
        ]);
    }

    // =========================================================================
    // ACTION & CRUD
    // =========================================================================

    public function create()
    {
        abort_if(Gate::denies('create-vehicle_damage_reports'), 403);
        $this->resetForm();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-vehicle_damage_reports'), 403);
        $this->validate();

        try {
            // Generate Kode Laporan unik otomatis (contoh: DMG-20231025-XXXX)
            $kodeLaporan = 'DMG-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            LaporanKerusakanMobil::create([ // 🔹 Diperbarui
                'kode_laporan' => $kodeLaporan,
                'mobil_id' => $this->mobil_id,
                'pengembalian_kode' => empty($this->pengembalian_kode) ? null : $this->pengembalian_kode,
                'deskripsi_kerusakan' => $this->deskripsi_kerusakan, // 🔹 Diperbarui
                'biaya_kerusakan' => $this->biaya_kerusakan, // 🔹 Diperbarui
            ]);

            $this->closeModal();
            $this->dispatch('notify', message: 'Laporan kerusakan baru berhasil dicatat.', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Gagal menyimpan: ' . $e->getMessage(), type: 'error');
        }
    }

    public function showDetail($kode)
    {
        $this->selectedReport = LaporanKerusakanMobil::with(['mobil', 'pengembalian.peminjaman.user'])->where('kode_laporan', $kode)->firstOrFail(); // 🔹 Diperbarui
        $this->showDetailModal = true;
    }

    public function edit($kode)
    {
        abort_if(Gate::denies('update-vehicle_damage_reports'), 403);
        $report = LaporanKerusakanMobil::where('kode_laporan', $kode)->firstOrFail(); // 🔹 Diperbarui
        
        $this->editingKode = $kode;
        $this->mobil_id = $report->mobil_id;
        $this->pengembalian_kode = $report->pengembalian_kode;
        $this->deskripsi_kerusakan = $report->deskripsi_kerusakan; // 🔹 Diperbarui
        $this->biaya_kerusakan = $report->biaya_kerusakan; // 🔹 Diperbarui
        
        $this->isEditMode = true;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-vehicle_damage_reports'), 403);
        $this->validate();

        try {
            $report = LaporanKerusakanMobil::where('kode_laporan', $this->editingKode)->firstOrFail(); // 🔹 Diperbarui
            
            $report->update([
                'deskripsi_kerusakan' => $this->deskripsi_kerusakan, // 🔹 Diperbarui
                'biaya_kerusakan' => $this->biaya_kerusakan, // 🔹 Diperbarui
            ]);

            $this->closeModal();
            $this->dispatch('notify', message: 'Laporan #' . $this->editingKode . ' berhasil diperbarui.', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Gagal: ' . $e->getMessage(), type: 'error');
        }
    }

    public function delete($kode)
    {
        abort_if(Gate::denies('delete-vehicle_damage_reports'), 403);
        LaporanKerusakanMobil::where('kode_laporan', $kode)->delete(); // 🔹 Diperbarui
        $this->dispatch('notify', message: 'Laporan kerusakan dihapus permanen.', type: 'warning');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showDetailModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        // 🔹 Diperbarui: damage_description dan damage_cost diganti
        $this->reset(['editingKode', 'mobil_id', 'pengembalian_kode', 'deskripsi_kerusakan', 'biaya_kerusakan', 'selectedReport', 'isEditMode']);
        $this->resetErrorBag();
    }
}