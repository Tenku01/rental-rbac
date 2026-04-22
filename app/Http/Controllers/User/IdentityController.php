<?php

// app/Http/Controllers/User/IdentityController.php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IdentityController extends Controller
{
    /**
     * Menampilkan form upload KTP dan SIM
     */
    public function showUploadForm()
    {
        $user = Auth::user();
        return view('user.identitas.upload_identity', ['userIdentification' => $user]);
    }

    /**
     * Menyimpan file KTP dan SIM
     */
    public function store(Request $request)
    {
        // Validasi dengan pesan custom
        $request->validate([
            'ktp' => 'required|image|mimes:jpg,png,jpeg|max:5120',
            'sim' => 'required|image|mimes:jpg,png,jpeg|max:5120',
        ], [
            'ktp.required' => 'Foto KTP wajib diunggah.',
            'ktp.image'    => 'File KTP harus berupa gambar.',
            'ktp.mimes'    => 'Format KTP harus jpg, png, atau jpeg.',
            'ktp.max'      => 'Ukuran KTP maksimal adalah 5MB.',
            'sim.required' => 'Foto SIM wajib diunggah.',
            'sim.image'    => 'File SIM harus berupa gambar.',
            'sim.mimes'    => 'Format SIM harus jpg, png, atau jpeg.',
            'sim.max'      => 'Ukuran SIM maksimal adalah 5MB.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $ktpPath = $request->file('ktp')->store('ktp', 'public');
        $simPath = $request->file('sim')->store('sim', 'public');

        $user->update([
            'foto_ktp' => $ktpPath,
            'foto_sim' => $simPath,
            'status_verifikasi' => 'menunggu', 
            'alasan_penolakan' => null,
        ]);

        return redirect()->route('dashboard')->with('success', 'KTP dan SIM berhasil diupload dan sedang ditinjau.');
    }

    /**
     * Menampilkan form edit KTP dan SIM
     */
    public function edit($id = null)
    {
        $user = Auth::user();
        return view('user.identitas.edit_identity', ['userIdentification' => $user]);
    }

    /**
     * Mengupdate data KTP dan SIM
     */
    public function update(Request $request, $id = null)
    {
        // Validasi dengan pesan custom (nullable karena opsional saat edit)
        $request->validate([
            'ktp' => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
            'sim' => 'nullable|image|mimes:jpg,png,jpeg|max:5120',
        ], [
            'ktp.image' => 'File KTP harus berupa gambar.',
            'ktp.mimes' => 'Format KTP harus jpg, png, atau jpeg.',
            'ktp.max'   => 'Ukuran KTP maksimal adalah 5MB.',
            'sim.image' => 'File SIM harus berupa gambar.',
            'sim.mimes' => 'Format SIM harus jpg, png, atau jpeg.',
            'sim.max'   => 'Ukuran SIM maksimal adalah 5MB.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $hasUpdates = false;

        if ($request->hasFile('ktp')) {
            if ($user->foto_ktp) {
                Storage::delete('public/' . $user->foto_ktp);
            }
            $user->foto_ktp = $request->file('ktp')->store('ktp', 'public');
            $hasUpdates = true;
        }

        if ($request->hasFile('sim')) {
            if ($user->foto_sim) {
                Storage::delete('public/' . $user->foto_sim);
            }
            $user->foto_sim = $request->file('sim')->store('sim', 'public');
            $hasUpdates = true;
        }

        if ($hasUpdates) {
            $user->status_verifikasi = 'menunggu';
            $user->alasan_penolakan = null;
        }

        $user->save();

        return redirect()->route('dashboard')->with('success', 'Data identitas berhasil diperbarui.');
    }

    /**
     * Menghapus data KTP dan SIM
     */
    public function destroy($id = null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->foto_ktp) Storage::delete('public/' . $user->foto_ktp);
        if ($user->foto_sim) Storage::delete('public/' . $user->foto_sim);

        $user->update([
            'foto_ktp' => null,
            'foto_sim' => null,
            'status_verifikasi' => 'belum_upload',
            'alasan_penolakan' => null,
        ]);

        return redirect()->route('dashboard')->with('success', 'Data identitas berhasil dihapus.');
    }
}