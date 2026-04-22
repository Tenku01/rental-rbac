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

class MobilIndex extends Component
{
    use WithPagination;
    use WithFileUploads;

    // --- Properties ---
    public $plat_nomor, $tipe, $merek, $warna, $transmisi, $kursi, $harga, $foto, $status = 'tersedia';
    public $id_asli;
    public $foto_lama;

    public $isEditMode = false;
    public $showModal = false;
    public $search = '';

    // --- Properti Khusus Modal Ubah Status (Untuk Staff) ---
    public $showStatusModal = false;
    public $status_edit = '';


 public function mount()
    {
        if (Gate::denies('read-mobils')) {
            return redirect()->route('unauthorized');
        }
    }
    // --- Validasi Realtime ---
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
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
        ];
    }

    // --- Custom Messages (Bahasa Indonesia) ---
    protected $messages = [
        'plat_nomor.required' => 'Plat Nomor wajib diisi.',
        'plat_nomor.regex' => 'Format salah! Gunakan format baku. Contoh: B 1234 XYZ',
        'plat_nomor.unique' => 'Plat Nomor ini sudah terdaftar di sistem.',
        'tipe.required' => 'Tipe mobil wajib diisi.',
        'tipe.max' => 'Tipe mobil maksimal 50 karakter.',
        'tipe.regex' => 'Tipe hanya boleh berisi huruf, angka, dan spasi.',
        'merek.required' => 'Merek mobil wajib diisi.',
        'merek.max' => 'Merek mobil maksimal 50 karakter.',
        'merek.regex' => 'Merek hanya boleh berisi huruf dan spasi (tanpa angka/simbol).',
        'warna.required' => 'Warna mobil wajib diisi.',
        'warna.max' => 'Warna mobil maksimal 30 karakter.',
        'warna.regex' => 'Warna hanya boleh berisi huruf dan spasi.',
        'transmisi.required' => 'Silakan pilih jenis transmisi.',
        'transmisi.in' => 'Pilihan transmisi tidak valid.',
        'kursi.required' => 'Silakan pilih jumlah kursi.',
        'kursi.in' => 'Pilihan jumlah kursi tidak valid.',
        'harga.required' => 'Harga sewa per hari wajib diisi.',
        'harga.numeric' => 'Harga sewa harus berupa angka.',
        'harga.min' => 'Harga sewa minimal Rp 10.000.',
        'status.required' => 'Status ketersediaan wajib dipilih.',
        'foto.required' => 'Foto armada wajib diunggah.',
        'foto.mimes' => 'Format file ditolak! Hanya izinkan JPG atau PNG.',
        'foto.max' => 'Ukuran foto maksimal 2MB.',
    ];

    #[Layout('layouts.admin')]
    public function render()
    {
        abort_if(Gate::denies('read-mobils'), 403);

        $mobils = Mobil::query()
            ->where('id', 'like', '%' . $this->search . '%')
            ->orWhere('merek', 'like', '%' . $this->search . '%')
            ->orWhere('tipe', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.menu.master.mobil-index', [
            'mobils' => $mobils
        ]); 
    }

    // =========================================================
    // CRUD FULL (KHUSUS ADMIN)
    // =========================================================

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function store()
    {
        abort_if(Gate::denies('create-mobils'), 403); 

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
        ]);

        $this->showModal = false;
        $this->resetInputFields();
        
        // Menggunakan Toast Notification
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
        
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function update()
    {
        abort_if(Gate::denies('update-mobils'), 403); 

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
        ]);

        $this->showModal = false;
        $this->resetInputFields();
        
        // Menggunakan Toast Notification
        $this->dispatch('show-toast', type: 'success', message: 'Data mobil berhasil diperbarui.');
    }

    public function delete($id)
    {
        abort_if(Gate::denies('delete-mobils'), 403); 

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

        $this->showStatusModal = false;
        $this->resetInputFields();
        $this->dispatch('show-toast', type: 'success', message: "Status {$mobil->id} berhasil diubah ke " . strtoupper($this->status_edit));
    }

    // =========================================================
    // UTILITIES
    // =========================================================

    private function resetInputFields()
    {
        $this->plat_nomor = '';
        $this->tipe = ''; $this->merek = ''; $this->warna = '';
        $this->transmisi = ''; $this->kursi = ''; $this->harga = '';
        $this->foto = null; $this->status = 'tersedia';
        $this->id_asli = null; $this->foto_lama = null;
        $this->status_edit = '';
        $this->resetValidation();
    }
}