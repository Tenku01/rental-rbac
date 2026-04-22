<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Mobil;
use Illuminate\Http\Request;

class MobilControllerUser extends Controller
{
    public function show($id)
    {
        $mobil = Mobil::findOrFail($id);
        return view('user.armada.mobil-detail', compact('mobil'));
    }

    public function mobil(Request $request)
    {
        // Query dasar mobil
        $query = Mobil::query();

        // 🔹 Cek apakah user sudah upload identitas & disetujui (hanya jika sudah login)
        $hasIdentification = false;
        if (Auth::check()) {
            $hasIdentification = Auth::user()->status_verifikasi === 'disetujui';
        }

        // 🔹 Filter jumlah kursi (jika ada)
        if ($request->filled('jumlah_kursi')) {
            $query->where('kursi', $request->jumlah_kursi);
        }

        // 🔹 Filter transmisi (jika ada)
        if ($request->filled('transmisi')) {
            $query->where('transmisi', $request->transmisi);
        }

        // 🔹 PERBAIKAN: Tambahkan ->withQueryString() di sini
        $mobils = $query->paginate(6)->withQueryString();

        // 🔹 Kirim data ke view
        return view('user.armada.mobil', compact('mobils', 'hasIdentification'));
    }
}