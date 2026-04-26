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

        // 🔹 Cek apakah user sudah upload identitas & disetujui
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

        // =========================================================================
        // 🔹 LOGIKA KATALOG BERDASARKAN ALUR "PILIH MOBIL DULU"
        // =========================================================================
        // Kita tampilkan semua mobil (termasuk yang sedang 'disewa') karena 
        // user bisa saja membooking untuk tanggal di masa depan.
        // Kita HANYA menyembunyikan armada yang sedang dalam 'pemeliharaan' (rusak/bengkel).
        $query->where('status', '!=', 'pemeliharaan');

        $mobils = $query->paginate(6)->withQueryString();

        return view('user.armada.mobil', compact('mobils', 'hasIdentification'));
    }
}