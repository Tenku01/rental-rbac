<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\TransaksiPembayaran; // 🔹 Diperbarui dari PaymentTransaction
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

// 🔹 Import library Midtrans
use Midtrans\Config;
use Midtrans\Snap;

class PengembalianController extends Controller
{
    /**
     * 🔹 User klik tombol "Selesaikan Peminjaman"
     * Membuat record pengembalian dengan kode unik.
     */
    public function store(Request $request, $peminjaman_id)
    {
        $peminjaman = Peminjaman::with('mobil')->findOrFail($peminjaman_id);

        // Cegah duplikasi pengembalian untuk satu transaksi peminjaman yang sama
        $existingPengembalian = Pengembalian::where('peminjaman_id', $peminjaman->id)->first();
        if ($existingPengembalian) {
            return redirect()->back()->with('warning', 'Permintaan pengembalian sudah dibuat sebelumnya.');
        }

        /**
         * 🔹 Generate kode pengembalian unik
         * Format: RET-PLATNOMOR-IDPEMINJAMAN
         * Contoh: RET-AB1111Y-104
         * Penambahan ID peminjaman menjamin kode unik meskipun mobilnya sama.
         */
        $platNomor = str_replace(' ', '', $peminjaman->mobil_id);
        $kodePengembalian = 'RET-' . strtoupper($platNomor) . '-' . $peminjaman->id;

        // Simpan pengembalian
        Pengembalian::create([
            'kode_pengembalian'   => $kodePengembalian,
            'peminjaman_id'       => $peminjaman->id,
            'tanggal_pengembalian'=> Carbon::now(),
            'status'              => 'menunggu pengecekan',
        ]);

        // Update status peminjaman menjadi selesai agar tidak muncul di daftar aktif user
        $peminjaman->update(['status' => 'selesai']);

        // Update status mobil agar segera dibersihkan oleh tim operasional
        if ($peminjaman->mobil) {
            $peminjaman->mobil->update(['status' => 'dibersihkan']);
        }

        return redirect()->back()->with(
            'success',
            'Mobil berhasil dikembalikan. Mohon tunggu pengecekan fisik oleh staff kami.'
        );
    }

    /**
     * 🔹 Legacy - pengecekan dipindahkan ke modul Staff
     */
    public function pengecekan(Request $request, $id)
    {
        return redirect()->back()->with(
            'warning',
            'Pengecekan kendaraan dikelola oleh Staff dan tidak diproses di modul ini.'
        );
    }

    /**
     * 🔹 Legacy - pembayaran denda tidak ditangani di tabel pengembalian
     */
    public function bayarDenda(Request $request, $id)
    {
        return redirect()->back()->with(
            'warning',
            'Pembayaran denda dikelola melalui modul transaksi.'
        );
    }

    /**
     * 🔹 Generate Snap Token Midtrans ASLI untuk Denda
     */
    public function generateSnapToken($kode_pengembalian)
    {
        $pengembalian = Pengembalian::with('peminjaman.user')
            ->where('kode_pengembalian', $kode_pengembalian)
            ->firstOrFail();

        // Total denda diambil dari relasi denda
        $totalDenda = $pengembalian->total_outstanding_fine ?? 0;

        if ($totalDenda <= 0) {
            return response()->json(['error' => 'Tidak ada denda yang perlu dibayar.'], 400);
        }

        if (Auth::id() !== $pengembalian->peminjaman->user_id) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        // 🔹 PEMBERSIHAN OTOMATIS: Hapus transaksi denda 'pending' sebelumnya agar tidak menumpuk di database
        TransaksiPembayaran::where('peminjaman_id', $pengembalian->peminjaman_id)
            ->where('tipe_transaksi', 'denda')
            ->where('status', 'pending')
            ->delete();

        // Pastikan order_id unik setiap kali request agar Midtrans tidak menolak jika terjadi kegagalan sebelumnya
        $orderId = 'DND-' . $pengembalian->kode_pengembalian . '-' . time();

        // Buat record transaksi pembayaran denda yang baru
        TransaksiPembayaran::create([
            'peminjaman_id' => $pengembalian->peminjaman_id,
            'id_transaksi_midtrans' => $orderId,
            'status' => 'pending',
            'jumlah' => $totalDenda,
            'tipe_transaksi' => 'denda',
        ]);

        // Update status pengembalian sementara menunggu pembayaran
        $pengembalian->update([
            'status' => 'menunggu_pembayaran_midtrans',
        ]);

        // =========================================================================
        // 🔹 Konfigurasi Midtrans
        // =========================================================================
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $user = $pengembalian->peminjaman->user;

        // Siapkan parameter transaksi untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $totalDenda, // Pastikan dikonversi menjadi integer
            ],
            'customer_details' => [
                'first_name' => $user->name ?? 'User',
                'email' => $user->email ?? 'no-reply@example.com',
                'phone' => $user->no_telp ?? $user->phone ?? '08123456789',
            ],
            'item_details' => [
                [
                    'id' => 'DENDA-' . $pengembalian->kode_pengembalian,
                    'price' => (int) $totalDenda,
                    'quantity' => 1,
                    'name' => 'Pembayaran Denda Pengembalian',
                ]
            ],
        ];

        try {
            // Meminta Snap Token asli ke server Midtrans
            $snapToken = Snap::getSnapToken($params);
            
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal membuat transaksi Midtrans: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 Dijalankan saat user menutup popup Midtrans tanpa membayar (onClose)
     * Menghapus transaksi yang batal / di-close agar tidak menjadi sampah data.
     */
    public function cancelMidtransPayment($kode_pengembalian)
    {
        $pengembalian = Pengembalian::where('kode_pengembalian', $kode_pengembalian)->first();
        
        if ($pengembalian) {
            // Hapus transaksi pembayaran denda yang masih pending
            TransaksiPembayaran::where('peminjaman_id', $pengembalian->peminjaman_id)
                ->where('tipe_transaksi', 'denda')
                ->where('status', 'pending')
                ->delete();

            // Kembalikan status pengembalian agar proses pembayaran bisa diulang nanti
            $pengembalian->update([
                'status' => 'selesai pengecekan'
            ]);

            return response()->json(['success' => true, 'message' => 'Transaksi Midtrans dibatalkan dan dihapus.']);
        }
        
        return response()->json(['error' => 'Data tidak ditemukan'], 404);
    }

    /**
     * 🔹 User memilih metode pembayaran manual (Transfer/Tunai)
     */
    public function selectManualPaymentMethod(Request $request, $kode_pengembalian)
    {
        $request->validate([
            'metode_pembayaran' => 'required|in:transfer,tunai',
        ]);

        $pengembalian = Pengembalian::with('peminjaman')
            ->where('kode_pengembalian', $kode_pengembalian)
            ->firstOrFail();

        if (Auth::id() !== $pengembalian->peminjaman->user_id) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $totalDenda = $pengembalian->total_outstanding_fine ?? 0;

        if ($totalDenda <= 0) {
            return redirect()->back()->with('error', 'Tidak ada denda yang perlu dibayar.');
        }

        $newStatus = $request->metode_pembayaran === 'tunai'
            ? 'menunggu_pembayaran_tunai'
            : 'menunggu_verifikasi_transfer';

        // Update status pengembalian sesuai pilihan metode
        $pengembalian->update([
            'status' => $newStatus,
        ]);

        return redirect()->back()->with(
            'success',
            'Pilihan pembayaran berhasil dicatat. Mohon segera selesaikan pembayaran dan tunggu konfirmasi staff.'
        );
    }
}