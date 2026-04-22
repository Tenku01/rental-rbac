<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\MidtransController;
use App\Models\TransaksiPembayaran;
use App\Models\PembatalanPesanan;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

/**
 * 🔹 SATU PINTU NOTIFIKASI MIDTRANS
 * Mas Tenku cukup daftarkan URL ini di Dashboard Midtrans:
 * https://semijuridic-selene-scrophulariaceous.ngrok-free.dev/api/payment/notification
 */
Route::post('/payment/notification', function (Request $request) {
    $data = $request->all();
    $status = $data['transaction_status'] ?? '';
    
    // 1. CEK APAKAH INI NOTIFIKASI REFUND?
    // Midtrans mengirim status 'refund' jika uang sudah balik ke pelanggan
    if ($status === 'refund') {
        $orderId = $data['order_id'] ?? '';
        $refundKey = $data['refund_key'] ?? '';

        // Cari di tabel TransaksiPembayaran kita (yang tipenya refund)
        $payment = TransaksiPembayaran::where('tipe_transaksi', 'refund')
            ->where(function($q) use ($orderId, $refundKey) {
                $q->where('id_transaksi_midtrans', $refundKey)
                  ->orWhere('id_transaksi_midtrans', $orderId);
            })
            ->first();

        if ($payment) {
            // Update status transaksi refund kita menjadi sukses (refunded)
            $payment->update([
                'status' => 'refunded',
                'respon_midtrans' => json_encode($data)
            ]);

            // Update status di tabel pembatalan_pesanan agar Admin tahu uang sudah cair
            $pembatalan = PembatalanPesanan::where('peminjaman_id', $payment->peminjaman_id)->first();
            if ($pembatalan) {
                $pembatalan->update(['status_pengembalian_dana' => 'refunded']);
            }

            return response()->json(['status' => 'OK', 'message' => 'Refund status updated']);
        }
    }

    // 2. JIKA BUKAN REFUND, MAKA INI PEMBAYARAN BIASA (Booking/Pelunasan)
    // Langsung oper ke Controller asli Mas Tenku agar logika booking tidak rusak
    return app(MidtransController::class)->notification($request);
});

// Route Frontend (Tetap sama)
Route::get('/payment/success', [MidtransController::class, 'success'])->name('payment.success');
Route::get('/payment/failed', [MidtransController::class, 'failed'])->name('payment.failed');
Route::get('/payment/unfinish', [MidtransController::class, 'unfinish'])->name('payment.unfinish');