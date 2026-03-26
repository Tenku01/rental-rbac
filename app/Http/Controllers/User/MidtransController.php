<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Peminjaman;
use App\Models\PaymentTransaction;
use Midtrans\Config;
use Midtrans\Snap;
use App\Mail\PaymentSuccessMail;
use Illuminate\Support\Facades\Mail;

class MidtransController extends Controller
{
    private function initMidtrans()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    // 🔹 Membuat transaksi (otomatis cek DP / LUNAS)
   public function pay(Peminjaman $peminjaman)
{
    $this->initMidtrans(); // tetap sandbox, tidak diubah

    $isDP = $peminjaman->tipe_pembayaran === 'dp';
    $orderType = strtoupper($isDP ? 'DP' : 'LUNAS');
    $orderId = $orderType . '-' . $peminjaman->id . '-' . time();
    $grossAmount = $isDP ? $peminjaman->dp_dibayarkan : $peminjaman->total_harga;

    $midtransParams = [
        'transaction_details' => [
            'order_id' => $orderId,
            'gross_amount' => $grossAmount,
        ],
        'customer_details' => [
            'first_name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ],
        'enabled_payments' => [
            'qris',            // <-- QRIS DITAMBAHKAN DI SINI
            'bank_transfer',
            'credit_card',
        ],
        'gopay' => [
            'enable_callback' => true,
        ],
        'qris' => [
            'acquirer' => 'gopay', 
        ],
    ];

    try {
        $snapToken = Snap::getSnapToken($midtransParams);

        PaymentTransaction::create([
            'peminjaman_id' => $peminjaman->id,
            'midtrans_transaction_id' => $orderId,
            'status' => 'pending',
            'amount' => $grossAmount,
            'tipe_transaksi' => $isDP ? 'dp' : 'lunas',
            'midtrans_response' => json_encode($midtransParams),
        ]);

        return response()->json(['snap_token' => $snapToken]);
    } catch (\Exception $e) {
        Log::error('Midtrans payment creation failed: ' . $e->getMessage());
        return response()->json([
            'error' => 'Gagal membuat transaksi: ' . $e->getMessage()
        ], 500);
    }
}

    // 🔹 Membuat transaksi Sisa Bayar
    public function paySisa(Peminjaman $peminjaman)
    {
        $this->initMidtrans();

        $orderId = 'SISA-' . $peminjaman->id . '-' . time();
        $grossAmount = $peminjaman->sisa_bayar;

        $midtransParams = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'email' => Auth::user()->email,
            ],
            'enabled_payments' => ['qris', 'bank_transfer', 'credit_card'],
            
        ];

        try {
            $snapToken = Snap::getSnapToken($midtransParams);

            PaymentTransaction::create([
                'peminjaman_id' => $peminjaman->id,
                'midtrans_transaction_id' => $orderId,
                'status' => 'pending',
                'amount' => $grossAmount,
                'tipe_transaksi' => 'sisa', // 🔹 tandai sebagai pelunasan
                'midtrans_response' => json_encode($midtransParams),
            ]);

            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal membuat transaksi sisa: ' . $e->getMessage()], 500);
        }
    }


    // 🔹 Notifikasi dari Midtrans (webhook)
    public function notification(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transactionStatus = $request->transaction_status;
        $orderId = $request->order_id;

        $payment = PaymentTransaction::where('midtrans_transaction_id', $orderId)->first();
        if (!$payment) return response()->json(['message' => 'Payment not found'], 404);

        $peminjaman = $payment->peminjaman;
        if (!$peminjaman) return response()->json(['message' => 'Peminjaman not found'], 404);

        switch ($transactionStatus) {
            case 'capture':
            case 'settlement':
                $payment->update(['status' => 'success']);

                if ($payment->tipe_transaksi === 'dp') {
                    $peminjaman->update([
                        'status' => 'pembayaran dp',
                        'dp_dibayarkan' => $payment->amount,
                        'total_dibayarkan' => $payment->amount,
                    ]);
                } else {
                    $peminjaman->update([
                        'status' => 'sudah dibayar lunas',
                        'dp_dibayarkan' => $peminjaman->total_harga,
                        'sisa_bayar' => 0,
                        'total_dibayarkan' => $peminjaman->total_harga,
                    ]);
                }
                
                try {
                    Mail::to($peminjaman->user->email)->send(new PaymentSuccessMail($payment));
                    Log::info("Email sukses dikirim ke " . $peminjaman->user->email);
                } catch (\Exception $e) {
                    Log::error("Gagal mengirim email: " . $e->getMessage());
                }

                break;

                break;

            case 'pending':
                $payment->update(['status' => 'pending']);
                break;

            case 'deny':
            case 'cancel':
            case 'expire':
                $payment->update(['status' => 'failed']);

                // 🔥 Hapus data peminjaman yang masih menunggu pembayaran
                if ($peminjaman->status === 'menunggu pembayaran') {
                    $peminjaman->delete();
                    Log::info("Peminjaman {$peminjaman->id} dihapus karena pembayaran gagal/batal.");
                }
                break;
        }

        return response()->json(['message' => 'Notification processed']);
    }
    public function cancelPayment(Peminjaman $peminjaman)
{
    try {
        // Hapus payment transaction yang masih pending
        PaymentTransaction::where('peminjaman_id', $peminjaman->id)
            ->where('status', 'pending')
            ->delete();

        // Jika kamu mau, bisa juga update status peminjaman:
        // $peminjaman->update(['status' => 'dibatalkan']);

        return response()->json(['message' => 'Transaksi dibatalkan.'], 200);
    } catch (\Exception $e) {
        Log::error('Cancel Payment Error: ' . $e->getMessage());
        return response()->json(['error' => 'Gagal membatalkan transaksi'], 500);
    }
}


    public function callback(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $orderId = $data['order_id'] ?? null;
        $status = $data['transaction_status'] ?? null;

        if (!$orderId) return response()->json(['message' => 'Invalid payload'], 400);

        $payment = PaymentTransaction::where('midtrans_transaction_id', $orderId)->first();
        if ($payment && $payment->peminjaman) {
            $peminjaman = $payment->peminjaman;

            $payment->update([
                'status' => $status,
                'midtrans_response' => json_encode($data),
            ]);

           if ($status === 'settlement') {
    if ($payment->tipe_transaksi === 'dp') {
        $peminjaman->update([
            'status' => 'pembayaran dp',
            'dp_dibayarkan' => $payment->amount,
            'total_dibayarkan' => $payment->amount,
        ]);
    } elseif ($payment->tipe_transaksi === 'sisa') {
        $peminjaman->update([
            'status' => 'sudah dibayar lunas',
            'sisa_bayar' => 0,
            'total_dibayarkan' => $peminjaman->dp_dibayarkan + $payment->amount,
        ]);
    } else { // transaksi langsung lunas
        $peminjaman->update([
            'status' => 'sudah dibayar lunas',
            'dp_dibayarkan' => $peminjaman->total_harga,
            'sisa_bayar' => 0,
            'total_dibayarkan' => $peminjaman->total_harga,
        ]);
            }
        }

        return response()->json(['message' => 'Callback processed', 'status' => $status]);
        }
    }

   // 🔹 Redirect URLs untuk Frontend
   // 🔹 Redirect URLs untuk Frontend
    public function success(Request $request) 
    { 
        // Ambil 1 transaksi pembayaran yang PALING BARU milik user yang sedang login
        $payment = PaymentTransaction::with(['peminjaman', 'peminjaman.mobil'])
            ->whereHas('peminjaman', function($query) {
                // Pastikan ini adalah transaksi milik user yang sedang login
                $query->where('user_id', auth::id());
            })
            ->latest('created_at') // Urutkan dari yang paling terakhir dibuat
            ->first(); // Ambil satu data saja

        return view('user.pesanan.success-payment', compact('payment')); 
    }
    public function failed() { return view('user.pesanan.failed-payment'); }
    public function unfinish() { return view('user.pesanan.unfinish-payment'); }
}
