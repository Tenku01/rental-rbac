<?php

namespace App\Http\Controllers\User;

use App\Models\Peminjaman;
use App\Models\PembatalanPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembatalanPesananController extends Controller
{
    /**
     * USER mengajukan pembatalan (LANGSUNG FINAL).
     * Peminjaman langsung berstatus 'dibatalkan' dan mobil kembali 'tersedia'.
     * Admin hanya memproses Refund (jika ada uang yang masuk).
     */
    public function store(Request $request, Peminjaman $peminjaman)
    {
        $request->validate([
            'alasan' => 'nullable|string|max:2000',
        ]);

        // Pastikan pemilik
        if ($peminjaman->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'error'   => 'Unauthorized'
            ], 403);
        }

        // Cegah pembatalan jika statusnya sudah selesai atau sudah dibatalkan sebelumnya
        if (in_array($peminjaman->status, ['selesai', 'dibatalkan'])) {
            return response()->json([
                'success' => false,
                'error'   => 'Pesanan ini sudah selesai atau sudah dibatalkan sebelumnya.'
            ], 422);
        }

        // LOGIKA REFUND YANG LEBIH AMAN: Cek berdasarkan nominal uang yang masuk
        // Jika user sudah bayar DP atau Lunas (total_dibayarkan > 0), maka statusnya 'pending_refund'
        // Jika belum bayar sama sekali (masih Rp 0), maka statusnya 'no_refund'
        $refundStatus = ($peminjaman->dp_dibayarkan > 0 || $peminjaman->total_dibayarkan > 0) 
                        ? 'pending_refund' 
                        : 'no_refund';

        try {
            DB::beginTransaction();

            // 1. Buat Record Pembatalan 
            // status_persetujuan langsung 'approved' karena pembatalan dari user mutlak berhasil
            $pembatalan = PembatalanPesanan::create([
                'peminjaman_id'            => $peminjaman->id,
                'user_id'                  => Auth::id(),
                'dibatalkan_oleh'          => 'user',
                'alasan'                   => $request->alasan,
                'status_pengembalian_dana' => $refundStatus,
                'dibatalkan_pada'          => now(),
                'status_persetujuan'       => 'approved', 
            ]);

            // 2. Ubah status Peminjaman menjadi dibatalkan
            $peminjaman->update([
                'status' => 'dibatalkan'
            ]);

            // 3. Bebaskan Mobil agar bisa disewa orang lain
            if ($peminjaman->mobil) {
                $peminjaman->mobil->update([
                    'status' => 'tersedia'
                ]);
            }

            DB::commit();

            // Pesan respon disesuaikan dengan kondisi refund
            $message = 'Pesanan berhasil dibatalkan.';
            if ($refundStatus === 'pending_refund') {
                $message .= ' Pengembalian dana (refund) Anda sedang diproses oleh admin.';
            }

            return response()->json([
                'success'         => true,
                'pembatalan_id'   => $pembatalan->id,
                'message'         => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membatalkan pesanan: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error'   => 'Terjadi kesalahan sistem saat membatalkan pesanan.'
            ], 500);
        }
    }
}