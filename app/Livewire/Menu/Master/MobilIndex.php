<?php
namespace App\Livewire\Menu\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Mobil;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon; // 🔹 Ditambahkan untuk tanggal

class MobilIndex extends Component
{
    use WithPagination;
    use WithFileUploads;

    // --- Properties Dasar ---
    public $plat_nomor, $tipe, $merek, $warna, $transmisi, $kursi, $harga, $foto, $status = 'tersedia';
    public $id_asli;
    public $foto_lama;

    // --- Properties Kepemilikan & Bagi Hasil ---
    public $status_kepemilikan = 'milik_sendiri';
    public $nama_pemilik;
    public $persentase_bagi_hasil_rental;
    public $persentase_bagi_hasil_mitra;

    public $isEditMode = false;
    public $showModal = false;
    public $search = '';
    
    // --- 🔹 Properti Filter Ketersediaan Tanggal ---
    public $filterTanggal;

    public $showStatusModal = false;
    public $status_edit = '';

    public function mount()
    {
        if (Gate::denies('read-mobil')) {
            return redirect()->route('unauthorized');
        }

        // 🔹 Set nilai default filter ke hari ini (Today)
        $this->filterTanggal = Carbon::today()->toDateString();
    }

    // --- Validasi & Kalkulasi Realtime ---
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // 🔹 Reset ke halaman pertama jika filter tanggal berubah
    public function updatedFilterTanggal()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // Fungsi otomatis menghitung persentase mitra ketika persentase rental diketik
    public function updatedPersentaseBagiHasilRental($value)
    {
        $val = (float) $value;
        if ($val > 100) $val = 100;
        if ($val < 0) $val = 0;
        
        $this->persentase_bagi_hasil_rental = $val;
        $this->persentase_bagi_hasil_mitra = 100 - $val;
    }
    
    // --- Validation Rules ---
    protected function rules()
    {
        return [
            'plat_nomor' => [
                'required',
                'string',
                Rule::unique('mobils', 'id')->ignore($this->id_asli, 'id'),
                'regex:/^[A-Z]{1,2}\s[0-9]{1,4}\s[A-Z]{1,3}$/'
            ],
            'tipe' => 'required|string|max:50|regex:/^[a-zA-Z0-9\s]+$/',
            'merek' => 'required|string|max:50|regex:/^[a-zA-Z\s]+$/',
            'warna' => 'required|string|max:30|regex:/^[a-zA-Z\s]+$/',
            'transmisi' => 'required|in:manual,otomatis',
            'kursi' => 'required|in:5,7,9,14,19',
            'harga' => 'required|numeric|min:10000',
            'status' => 'required|in:tersedia,disewa,pemeliharaan,dibersihkan',
            'foto' => $this->isEditMode ? 'nullable|mimes:jpg,jpeg,png|max:2048' : 'required|mimes:jpg,jpeg,png|max:2048',
            
            // Validasi Kepemilikan
            'status_kepemilikan' => 'required|in:milik_sendiri,mitra',
            'nama_pemilik' => 'required_if:status_kepemilikan,mitra|nullable|string|max:255',
            'persentase_bagi_hasil_rental' => 'required_if:status_kepemilikan,mitra|nullable|numeric|min:0|max:100',
        ];
    }

    // --- Custom Messages (Bahasa Indonesia) ---
    protected $messages = [
        'plat_nomor.required' => 'Plat Nomor wajib diisi.',
        'plat_nomor.regex' => 'Format salah! Gunakan format baku. Contoh: B 1234 XYZ',
        'plat_nomor.unique' => 'Plat Nomor ini sudah terdaftar di sistem.',
        'tipe.required' => 'Tipe mobil wajib diisi.',
        'merek.required' => 'Merek mobil wajib diisi.',
        'warna.required' => 'Warna mobil wajib diisi.',
        'transmisi.required' => 'Silakan pilih jenis transmisi.',
        'kursi.required' => 'Silakan pilih jumlah kursi.',
        'harga.required' => 'Harga sewa per hari wajib diisi.',
        'status.required' => 'Status ketersediaan wajib dipilih.',
        'foto.required' => 'Foto armada wajib diunggah.',
        'foto.mimes' => 'Format file ditolak! Hanya izinkan JPG atau PNG.',
        'foto.max' => 'Ukuran foto maksimal 2MB.',
        
        'nama_pemilik.required_if' => 'Nama pemilik wajib diisi jika status mobil adalah Mitra.',
        'persentase_bagi_hasil_rental.required_if' => 'Persentase bagi hasil wajib ditentukan.',
        'persentase_bagi_hasil_rental.max' => 'Persentase maksimal 100%.',
    ];

    #[Layout('layouts.admin')]
    public function render()
    {
        abort_if(Gate::denies('read-mobil'), 403);

        $query = Mobil::query();

        // 🔹 LOGIKA FILTER TANGGAL: 
        // Hanya tampilkan mobil yang TIDAK ADA (doesntHave) di tabel peminjaman pada tanggal yang dipilih
        // (Selesai dan Dibatalkan diabaikan karena mobil dianggap kosong lagi)
        if ($this->filterTanggal) {
            $query->whereDoesntHave('peminjaman', function ($q) {
                $q->where('tanggal_sewa', '<=', $this->filterTanggal)
                  ->where('tanggal_kembali', '>=', $this->filterTanggal)
                  ->whereNotIn('status', ['selesai', 'dibatalkan']);
            });
        }

        // Pencarian teks biasa
        $query->where(function ($q) {
            $q->where('id', 'like', '%' . $this->search . '%')
              ->orWhere('merek', 'like', '%' . $this->search . '%')
              ->orWhere('tipe', 'like', '%' . $this->search . '%');
        });

        $mobils = $query->orderBy('created_at', 'desc')
                        ->paginate(10)
                        ->withPath(url()->current());

        return view('livewire.menu.master.mobil-index', [
            'mobils' => $mobils
        ]); 
    }

    // =========================================================
    // CRUD FULL (KHUSUS ADMIN)
    // =========================================================

    public function create()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-mobil'), 403); 

        $this->plat_nomor = strtoupper(trim($this->plat_nomor));
        $this->validate();

        $fotoPath = null;
        if ($this->foto) {
            $fotoPath = $this->foto->store('mobils', 'public');
        }

        Mobil::create([
            'id' => $this->plat_nomor,
            'tipe' => $this->tipe,
            'merek' => $this->merek,
            'warna' => $this->warna,
            'transmisi' => $this->transmisi,
            'kursi' => $this->kursi,
            'harga' => $this->harga,
            'foto' => $fotoPath,
            'status' => $this->status,
            'status_kepemilikan' => $this->status_kepemilikan,
            'nama_pemilik' => $this->status_kepemilikan === 'mitra' ? $this->nama_pemilik : null,
            'persentase_bagi_hasil_rental' => $this->status_kepemilikan === 'mitra' ? $this->persentase_bagi_hasil_rental : null,
            'persentase_bagi_hasil_mitra' => $this->status_kepemilikan === 'mitra' ? $this->persentase_bagi_hasil_mitra : null,
        ]);

        $this->closeModal();
        $this->dispatch('show-toast', type: 'success', message: 'Mobil baru berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $mobil = Mobil::findOrFail($id);
        
        $this->id_asli = $id;
        $this->plat_nomor = $mobil->id;
        $this->tipe = $mobil->tipe;
        $this->merek = $mobil->merek;
        $this->warna = $mobil->warna;
        $this->transmisi = $mobil->transmisi;
        $this->kursi = $mobil->kursi;
        $this->harga = $mobil->harga;
        $this->status = $mobil->status;
        $this->foto_lama = $mobil->foto;
        
        // Populate data kepemilikan
        $this->status_kepemilikan = $mobil->status_kepemilikan ?? 'milik_sendiri';
        $this->nama_pemilik = $mobil->nama_pemilik;
        $this->persentase_bagi_hasil_rental = $mobil->persentase_bagi_hasil_rental;
        $this->persentase_bagi_hasil_mitra = $mobil->persentase_bagi_hasil_mitra;
        
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-mobil'), 403); 

        $this->plat_nomor = strtoupper(trim($this->plat_nomor));
        $this->validate();

        $mobil = Mobil::findOrFail($this->id_asli);

        $fotoPath = $mobil->foto;
        if ($this->foto) {
            if ($mobil->foto && !str_contains($mobil->foto, 'http')) {
                Storage::disk('public')->delete($mobil->foto);
            }
            $fotoPath = $this->foto->store('mobils', 'public');
        }

        $mobil->update([
            'id' => $this->plat_nomor,
            'tipe' => $this->tipe,
            'merek' => $this->merek,
            'warna' => $this->warna,
            'transmisi' => $this->transmisi,
            'kursi' => $this->kursi,
            'harga' => $this->harga,
            'foto' => $fotoPath,
            'status' => $this->status,
            'status_kepemilikan' => $this->status_kepemilikan,
            'nama_pemilik' => $this->status_kepemilikan === 'mitra' ? $this->nama_pemilik : null,
            'persentase_bagi_hasil_rental' => $this->status_kepemilikan === 'mitra' ? $this->persentase_bagi_hasil_rental : null,
            'persentase_bagi_hasil_mitra' => $this->status_kepemilikan === 'mitra' ? $this->persentase_bagi_hasil_mitra : null,
        ]);

        $this->closeModal();
        $this->dispatch('show-toast', type: 'success', message: 'Data mobil berhasil diperbarui.');
    }

    public function delete($id)
    {
        abort_if(Gate::denies('delete-mobil'), 403); 

        try {
            $mobil = Mobil::findOrFail($id);
            
            if ($mobil->peminjaman()->exists()) {
                $this->dispatch('show-toast', type: 'error', message: 'Gagal Hapus: Mobil ini memiliki riwayat transaksi aktif.');
                return;
            }

            if ($mobil->foto && !str_contains($mobil->foto, 'http')) {
                Storage::disk('public')->delete($mobil->foto);
            }

            $mobil->delete();
            $this->dispatch('show-toast', type: 'success', message: 'Data armada berhasil dihapus.');

        } catch (\Exception $e) {
            $this->dispatch('show-toast', type: 'error', message: 'Terjadi kesalahan sistem saat menghapus data.');
        }
    }

    // =========================================================
    // QUICK EDIT STATUS (BISA DIAKSES STAFF & ADMIN)
    // =========================================================

    public function openStatusModal($id)
    {
        $mobil = Mobil::findOrFail($id);
        $this->id_asli = $mobil->id;
        $this->status_edit = $mobil->status;
        $this->showStatusModal = true;
    }

    public function updateStatusOnly()
    {
        $this->validate([
            'status_edit' => 'required|in:tersedia,pemeliharaan,dibersihkan',
        ]);

        $mobil = Mobil::findOrFail($this->id_asli);
        $mobil->update(['status' => $this->status_edit]);

        $this->closeModal();
        $this->dispatch('show-toast', type: 'success', message: "Status {$mobil->id} berhasil diubah ke " . strtoupper($this->status_edit));
    }

    // =========================================================
    // UTILITIES
    // =========================================================
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->showStatusModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'plat_nomor', 'tipe', 'merek', 'warna',
            'transmisi', 'kursi', 'harga', 'foto',
            'id_asli', 'foto_lama', 'status_edit',
            'nama_pemilik', 'persentase_bagi_hasil_rental', 'persentase_bagi_hasil_mitra'
        ]);
        
        $this->status = 'tersedia';
        $this->status_kepemilikan = 'milik_sendiri';
        $this->resetErrorBag();
        $this->resetValidation();
    }
}