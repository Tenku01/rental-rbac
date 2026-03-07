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
    public $plat_nomor, $tipe, $merek, $warna, $transmisi, $kursi, $harga, $foto, $status;
    public $id_asli;
    public $foto_lama;

    public $isEditMode = false;
    public $showModal = false;
    public $search = '';

    // --- Properti Khusus Modal Ubah Status (Untuk Staff) ---
    public $showStatusModal = false;
    public $status_edit = '';

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

    protected $messages = [
        'plat_nomor.regex' => 'Format Plat Nomor salah! Gunakan HURUF KAPITAL dan SPASI. Contoh: B 1234 XYZ',
        'plat_nomor.unique' => 'Plat Nomor ini sudah terdaftar.',
    ];

    #[Layout('layouts.admin')]
    public function render()
    {
        // Minimal harus punya akses read-mobils
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
        abort_if(Gate::denies('create-mobils'), 403); // RBAC Protect

        $this->plat_nomor = strtoupper($this->plat_nomor);
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
        session()->flash('message', 'Mobil berhasil ditambahkan.');
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
        abort_if(Gate::denies('update-mobils'), 403); // RBAC Protect

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
        session()->flash('message', 'Data mobil berhasil diperbarui.');
    }

    public function delete($id)
    {
        abort_if(Gate::denies('delete-mobils'), 403); // RBAC Protect

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
        // Karena Staff sudah bisa masuk halaman ini (Gate read-mobils lolos),
        // Kita izinkan perubahan status operasional saja.
        $this->validate([
            'status_edit' => 'required|in:tersedia,pemeliharaan,dibersihkan',
        ]);

        $mobil = Mobil::findOrFail($this->id_asli);
        $mobil->update(['status' => $this->status_edit]);

        $this->showStatusModal = false;
        $this->resetInputFields();
        session()->flash('message', 'Status armada ' . $mobil->id . ' berhasil diubah menjadi ' . strtoupper($this->status_edit));
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
    }
}