<?php

namespace App\Livewire\Menu\Operasional;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\InspeksiMobil; 
use App\Models\Pengembalian;
use App\Models\Peminjaman; 
use App\Models\LaporanKerusakanMobil; 
use App\Models\Denda; 
use App\Models\Mobil;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InspeksiMobilIndex extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $filterKondisi = ''; 

    // --- Modal State (Inspeksi Akhir / Pasca Pengembalian) ---
    public $showModal = false;
    public $showDetailModal = false;
    public $selectedInspection = null;

    // Form Fields Inspeksi Akhir
    public $kode_pengembalian; // Disesuaikan dengan DB
    public $inspection_date;
    public $kondisi = 'Baik Sempurna'; 
    public $notes;
    public $photo;
    public $infoPenyewa, $infoMobil, $jadwalKembali, $hargaPerHari;
    public $jamTerlambat = 0;
    public $lateFine = 0;
    public $isDamaged = false;
    public $deskripsi_kerusakan; 
    public $biaya_kerusakan = 0; 

    // --- Modal State (Inspeksi Awal / Penyerahan Mobil) ---
    public $showPreModal = false;
    
    // Form Fields Inspeksi Awal
    public $pre_peminjaman_id;
    public $pre_infoPenyewa;
    public $pre_infoMobil;
    public $pre_kondisi = '';

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        if (Gate::denies('read-vehicle_inspections')) {
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
        // Jika sedang membuka modal Pre-Inspection (Inspeksi Awal)
        if ($this->showPreModal) {
            return [
                'pre_kondisi' => 'required|string|min:5'
            ];
        }

        // Rules untuk Inspeksi Akhir
        $rules = [
            'kode_pengembalian' => 'required', // Diubah dari pengembalian_id
            'inspection_date' => 'required|date',
            'kondisi' => 'required|in:Baik Sempurna,Perlu Perbaikan Ringan,Rusak Berat',
            'notes' => 'required|string|min:5',
            'photo' => 'nullable|image|max:2048'
        ];

        if ($this->isDamaged) {
            $rules['deskripsi_kerusakan'] = 'required|string|min:10'; 
            $rules['biaya_kerusakan'] = 'required|numeric|min:0'; 
        }

        return $rules;
    }

    protected function messages()
    {
        return [
            // Pesan Inspeksi Awal
            'pre_kondisi.required' => 'Catatan kondisi awal kendaraan wajib diisi.',
            'pre_kondisi.min' => 'Catatan terlalu singkat (minimal 5 karakter).',
            
            // Pesan Inspeksi Akhir
            'kode_pengembalian.required' => 'Sistem kehilangan referensi Kode Pengembalian.', // Diubah
            'inspection_date.required' => 'Waktu audit wajib ditentukan.',
            'kondisi.required' => 'Kondisi umum kendaraan wajib dipilih.', 
            'notes.required' => 'Catatan audit internal wajib diisi.',
            'notes.min' => 'Catatan terlalu singkat (minimal 5 karakter).',
            'photo.image' => 'File bukti harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
            'deskripsi_kerusakan.required' => 'Deskripsi rincian kerusakan wajib diisi.', 
            'deskripsi_kerusakan.min' => 'Deskripsi kerusakan minimal 10 karakter.', 
            'biaya_kerusakan.required' => 'Estimasi biaya perbaikan wajib diisi.', 
            'biaya_kerusakan.numeric' => 'Estimasi biaya harus berupa angka tanpa titik/koma.', 
        ];
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        // 1. Query: Antrean Inspeksi Awal (Penyerahan)
        $pendingDepartures = Peminjaman::with(['user', 'mobil'])
            ->where('status', 'sudah dibayar lunas')
            ->orderBy('tanggal_sewa', 'asc')
            ->get();

        // 2. Query: Antrean Inspeksi Akhir (Pengembalian)
        $pendingReturns = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
            ->where('status', 'menunggu pengecekan')
            ->orderBy('tanggal_pengembalian', 'asc')
            ->get();

        // 3. Query: Riwayat Inspeksi Akhir
        $inspections = InspeksiMobil::with(['mobil', 'pemeriksa', 'pengembalian.peminjaman.user'])
            ->when($this->search, function($q) {
                $q->whereHas('mobil', fn($sq) => $sq->where('id', 'like', '%'.$this->search.'%'));
            })
            ->when($this->filterKondisi, fn($q) => $q->where('kondisi', $this->filterKondisi)) 
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.menu.operasional.inspeksi-mobil-index', [
            'pendingDepartures' => $pendingDepartures,
            'pendingReturns' => $pendingReturns,
            'inspections' => $inspections,
        ]);
    }

    // =========================================================================
    // FITUR: INSPEKSI AWAL (PENYERAHAN MOBIL / PRE-RENTAL)
    // =========================================================================

    public function openPreInspection($id)
    {
        abort_if(Gate::denies('create-vehicle_inspections'), 403);
        $this->resetPreForm();
        
        $peminjaman = Peminjaman::with(['user', 'mobil'])->findOrFail($id);
        
        $this->pre_peminjaman_id = $id;
        $this->pre_infoPenyewa = $peminjaman->user->name ?? 'N/A';
        $this->pre_infoMobil = ($peminjaman->mobil->merek ?? 'Mobil') . ' [' . ($peminjaman->mobil->id ?? 'N/A') . ']';
        
        // LOGIKA SPLIT CATATAN
        $fullKondisi = $peminjaman->kondisi_mobil ?? '';
        $parts = explode("\n\n[+] Tambahan Validasi Pelanggan", $fullKondisi);
        
        // Tampilkan HANYA bagian Staff
        $this->pre_kondisi = trim($parts[0]);
        
        $this->showPreModal = true;
    }

    public function storePreInspection()
    {
        abort_if(Gate::denies('create-vehicle_inspections'), 403);

        $this->validate(); 

        DB::beginTransaction();
        try {
            $peminjaman = Peminjaman::findOrFail($this->pre_peminjaman_id);
            
            // LOGIKA MERGE CATATAN
            $fullKondisi = $peminjaman->kondisi_mobil ?? '';
            $parts = explode("\n\n[+] Tambahan Validasi Pelanggan", $fullKondisi);
            
            $newKondisi = trim($this->pre_kondisi);
            if (count($parts) > 1) {
                $newKondisi .= "\n\n[+] Tambahan Validasi Pelanggan" . $parts[1];
            }

            // 1. Catat kondisi mobil
            $peminjaman->update([
                'kondisi_mobil' => $newKondisi
            ]);

            // 2. Pastikan mobil statusnya Disewa
            if ($peminjaman->mobil) {
                $peminjaman->mobil->update(['status' => 'disewa']);
            }

            DB::commit();
            $this->closePreModal();
            $this->dispatch('notify', message: 'Catatan kondisi awal berhasil disimpan. Menunggu pelanggan mengubah status menjadi Berlangsung.', type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Gagal memproses data: ' . $e->getMessage(), type: 'error');
        }
    }

    public function closePreModal()
    {
        $this->showPreModal = false;
        $this->resetPreForm();
    }

    private function resetPreForm()
    {
        $this->reset(['pre_peminjaman_id', 'pre_kondisi', 'pre_infoPenyewa', 'pre_infoMobil']);
        $this->resetErrorBag();
    }


    // =========================================================================
    // FITUR: INSPEKSI AKHIR (PENGEMBALIAN / POST-RENTAL)
    // =========================================================================

    // Diubah parameter penangkapan dari $returnId ke $kodePengembalian string
    public function createInspection($kodePengembalian)
    {
        abort_if(Gate::denies('create-vehicle_inspections'), 403);
        $this->resetForm();
        
        // Cari berdasarkan kode_pengembalian, bukan ID
        $return = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
                    ->where('kode_pengembalian', $kodePengembalian)
                    ->firstOrFail();
        
        $this->kode_pengembalian = $kodePengembalian;
        $this->infoPenyewa = $return->peminjaman->user->name ?? 'N/A';
        $this->infoMobil = ($return->peminjaman->mobil->merek ?? 'Mobil') . ' [' . ($return->peminjaman->mobil->id ?? 'N/A') . ']';
        $this->hargaPerHari = $return->peminjaman->mobil->harga ?? 0;
        
        $waktuSeharusnya = Carbon::parse($return->peminjaman->tanggal_kembali . ' ' . $return->peminjaman->jam_sewa);
        $waktuPengembalian = Carbon::parse($return->tanggal_pengembalian); // Diperbarui: Menggunakan waktu aktual pengembalian

        // Perbandingan antara jadwal kembali dan waktu pengembalian aktual
        if ($waktuPengembalian->gt($waktuSeharusnya)) {
            $this->jamTerlambat = ceil($waktuSeharusnya->diffInHours($waktuPengembalian));
            $this->lateFine = ($this->hargaPerHari * 0.1) * $this->jamTerlambat;
        } else {
            $this->jamTerlambat = 0;
            $this->lateFine = 0;
        }

        $this->inspection_date = now()->format('Y-m-d H:i');
        $this->showModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-vehicle_inspections'), 403);

        $this->validate();

        DB::beginTransaction();
        try {
            // Gunakan where kode_pengembalian
            $return = Pengembalian::with('peminjaman')
                        ->where('kode_pengembalian', $this->kode_pengembalian)
                        ->firstOrFail();
            
            // 1. Simpan Log Inspeksi Akhir
            InspeksiMobil::create([
                'mobil_id' => $return->peminjaman->mobil_id,
                'pemeriksa_id' => Auth::id(), 
                'pengembalian_kode' => $return->kode_pengembalian,
                'kondisi' => $this->kondisi, 
                'keterangan' => $this->notes, 
            ]);

            $totalDendaKerusakan = 0;

            // 2. Simpan Laporan Kerusakan (jika ada)
            if ($this->isDamaged && $this->biaya_kerusakan > 0) { 
                $platNo = str_replace(' ', '', $return->peminjaman->mobil_id);
                // Karena kita tidak punya ID, kita pakai timestamp yang lebih unik atau dari kode pengembalian
                $kodeLaporan = 'DMG-' . strtoupper($platNo) . '-' . substr($return->kode_pengembalian, -4) . '-' . date('His');

                LaporanKerusakanMobil::create([ 
                    'kode_laporan' => $kodeLaporan,
                    'mobil_id' => $return->peminjaman->mobil_id,
                    'pengembalian_kode' => $return->kode_pengembalian,
                    'deskripsi_kerusakan' => $this->deskripsi_kerusakan, 
                    'biaya_kerusakan' => $this->biaya_kerusakan, 
                ]);

                $totalDendaKerusakan = $this->biaya_kerusakan; 
            }

            // 3. Simpan ke Tabel Fines (Denda Terpadu)
            if ($this->lateFine > 0 || $totalDendaKerusakan > 0) {
                Denda::create([ 
                    'peminjaman_id' => $return->peminjaman_id,
                    'denda_keterlambatan' => $this->lateFine,
                    'denda_kerusakan' => $totalDendaKerusakan,
                    'total_denda' => $this->lateFine + $totalDendaKerusakan,
                    'status' => 'belum dibayar',
                    'tanggal_terdeteksi' => now()->toDateString(),
                    'keterangan' => "Denda untuk pengembalian {$return->kode_pengembalian}. (Kerusakan: Rp" . number_format($totalDendaKerusakan) . ", Terlambat: {$this->jamTerlambat} Jam)"
                ]);

                // pastikan kolom 'denda' ada di tabel pengembalian, jika tidak baris ini bisa di-comment
                // $return->update(['denda' => $this->lateFine + $totalDendaKerusakan]); 
            }

            // 4. Update Status Pengembalian & Mobil
            $return->update(['status' => 'sudah dicek']);
            
            $mobilStatus = ($this->kondisi === 'Baik Sempurna') ? 'tersedia' : 'pemeliharaan'; 
            Mobil::where('id', $return->peminjaman->mobil_id)->update(['status' => $mobilStatus]);

            DB::commit();
            $this->closeModal();
            $this->dispatch('notify', message: 'Finalisasi pengecekan akhir berhasil!', type: 'success');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', message: 'Gagal memproses data: ' . $e->getMessage(), type: 'error');
        }
    }

    public function showDetail($id)
    {
        $this->selectedInspection = InspeksiMobil::with(['mobil', 'pemeriksa', 'pengembalian.peminjaman.user'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function delete($id)
    {
        abort_if(Gate::denies('delete-vehicle_inspections'), 403);
        $inspection = InspeksiMobil::findOrFail($id); 
        
        $inspection->delete();
        $this->dispatch('notify', message: 'Riwayat inspeksi berhasil dihapus.', type: 'warning');
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
            'kode_pengembalian', 'kondisi', 'notes', 'photo', 'isDamaged', // Diubah dari pengembalian_id
            'deskripsi_kerusakan', 'biaya_kerusakan', 'jamTerlambat', 'lateFine', 'selectedInspection' 
        ]);
        $this->resetErrorBag();
    }
}