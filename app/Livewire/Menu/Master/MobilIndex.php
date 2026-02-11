<?php
namespace App\Livewire\Menu\Master;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Mobil;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule; // Tambahkan ini untuk validasi unique

class MobilIndex extends Component
{
    use WithPagination;
    use WithFileUploads;

    // --- Properties ---
    // Saya ganti $mobil_id menjadi $id_asli untuk menyimpan referensi saat edit
    // Tambahkan $plat_nomor untuk input user
    public $plat_nomor, $tipe, $merek, $warna, $transmisi, $kursi, $harga, $foto, $status;
    public $id_asli; // Untuk menyimpan ID lama saat proses edit
    public $foto_lama;

    public $isEditMode = false;
    public $showModal = false;
    public $search = '';

    // --- Validation Rules ---
    protected function rules()
    {
        return [
            // Validasi Plat Nomor (ID)
            'plat_nomor' => [
                'required',
                'string',
                // Cek unique ke tabel mobils kolom id. 
                // Jika edit mode, abaikan ID yang sedang diedit.
                Rule::unique('mobils', 'id')->ignore($this->id_asli, 'id'),
                // REGEX PLAT NOMOR INDONESIA
                // Format: Huruf(1-2) Spasi Angka(1-4) Spasi Huruf(1-3)
                // Contoh: B 1234 TJE, AB 1234 XY
                'regex:/^[A-Z]{1,2}\s[0-9]{1,4}\s[A-Z]{1,3}$/'
            ],
            'tipe' => 'required|string',
            'merek' => 'required|string',
            'warna' => 'required|string',
            'transmisi' => 'required|in:manual,otomatis',
            'kursi' => 'required|in:5,7,9,14,19',
            'harga' => 'required|numeric|min:0',
            'status' => 'required|in:tersedia,disewa,pemeliharaan,dibersihkan',
            'foto' => $this->isEditMode ? 'nullable|image|max:2048' : 'required|image|max:2048',
        ];
    }

    // Custom Error Messages agar lebih ramah
    protected $messages = [
        'plat_nomor.regex' => 'Format Plat Nomor salah! Gunakan HURUF KAPITAL dan SPASI. Contoh: B 1234 XYZ',
        'plat_nomor.unique' => 'Plat Nomor ini sudah terdaftar.',
    ];

    #[Layout('layouts.admin')]
    public function render()
    {
        $mobils = Mobil::query()
            ->where('id', 'like', '%' . $this->search . '%') // Cari berdasarkan Plat Nomor
            ->orWhere('merek', 'like', '%' . $this->search . '%')
            ->orWhere('tipe', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.admin.mobil-index', [
            'mobils' => $mobils
        ]); 
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function store()
    {
        // Paksa Plat Nomor jadi Huruf Besar sebelum validasi
        $this->plat_nomor = strtoupper($this->plat_nomor);

        $this->validate();

        $fotoPath = null;
        if ($this->foto) {
            $fotoPath = $this->foto->store('mobils', 'public');
        }

        Mobil::create([
            'id' => $this->plat_nomor, // Simpan Plat Nomor ke kolom ID
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
        session()->flash('message', 'Mobil berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $mobil = Mobil::findOrFail($id);
        
        $this->id_asli = $id; // Simpan ID asli (Plat Nomor lama)
        $this->plat_nomor = $mobil->id; // Tampilkan Plat Nomor di input
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
        // Paksa Plat Nomor jadi Huruf Besar
        $this->plat_nomor = strtoupper($this->plat_nomor);
        
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
            'id' => $this->plat_nomor, // Update Plat Nomor jika diubah
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
        session()->flash('message', 'Data mobil berhasil diperbarui.');
    }

    public function delete($id)
    {
        try {
            $mobil = Mobil::findOrFail($id);
            
            if ($mobil->peminjaman()->exists()) {
                session()->flash('error', 'GAGAL HAPUS: Mobil ini memiliki riwayat transaksi.');
                return;
            }

            if ($mobil->foto && !str_contains($mobil->foto, 'http')) {
                Storage::disk('public')->delete($mobil->foto);
            }

            $mobil->delete();
            session()->flash('message', 'Mobil berhasil dihapus.');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function resetInputFields()
    {
        $this->plat_nomor = ''; // Reset plat nomor
        $this->tipe = ''; $this->merek = ''; $this->warna = '';
        $this->transmisi = ''; $this->kursi = ''; $this->harga = '';
        $this->foto = null; $this->status = 'tersedia';
        $this->id_asli = null; $this->foto_lama = null;
    }
}