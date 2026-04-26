<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Peminjaman;
use App\Models\Pengembalian; 
use App\Models\Denda; // 🔹 Ditambahkan untuk mengupdate langsung tabel denda
use App\Models\TransaksiPembayaran; 
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
                'qris',            
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

            TransaksiPembayaran::create([
                'peminjaman_id' => $peminjaman->id,
                'id_transaksi_midtrans' => $orderId, 
                'status' => 'pending',
                'jumlah' => $grossAmount,            
                'tipe_transaksi' => $isDP ? 'dp' : 'lunas',
                'respon_midtrans' => json_encode($midtransParams), 
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

            TransaksiPembayaran::create([
                'peminjaman_id' => $peminjaman->id,
                'id_transaksi_midtrans' => $orderId, 
                'status' => 'pending',
                'jumlah' => $grossAmount,            
                'tipe_transaksi' => 'sisa', 
                'respon_midtrans' => json_encode($midtransParams), 
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

        $payment = TransaksiPembayaran::where('id_transaksi_midtrans', $orderId)->first(); 
        if (!$payment) return response()->json(['message' => 'Payment not found'], 404);

        $peminjaman = $payment->peminjaman;
        if (!$peminjaman) return response()->json(['message' => 'Peminjaman not found'], 404);

        // 🔹 Normalisasi tipe transaksi (Anti spasi / huruf kapital)
        $tipeTransaksi = strtolower(trim($payment->tipe_transaksi));

        switch ($transactionStatus) {
            case 'capture':
            case 'settlement':
                $payment->update(['status' => 'success']);

                if ($tipeTransaksi === 'dp') {
                    $peminjaman->update([
                        'status' => 'pembayaran dp',
                        'dp_dibayarkan' => $payment->jumlah,       
                        'total_dibayarkan' => $payment->jumlah,    
                    ]);
                } elseif ($tipeTransaksi === 'denda') {
                    // 1. Update Tabel Pengembalian
                    $pengembalian = Pengembalian::where('peminjaman_id', $peminjaman->id)->first();
                    if ($pengembalian) {
                        // Status disesuaikan persis seperti permintaan ENUM Anda
                        $pengembalian->status = 'sudah di cek dan denda dibayarkan';
                        $pengembalian->save(); 
                    }

                    // 2. 🔹 Update Tabel Denda (Bypass $fillable dengan save())
                    $denda = Denda::where('peminjaman_id', $peminjaman->id)
                        ->where('status', 'belum dibayar')
                        ->first();
                    
                    if ($denda) {
                        $denda->status = 'sudah dibayar';
                        $denda->tanggal_pembayaran = now();
                        $denda->metode_pembayaran = 'midtrans';
                        $denda->save();
                    }

                    // 3. Kunci status peminjaman agar TETAP "selesai" (mencegah bug berubah jadi lunas)
                    $peminjaman->update([
                        'status' => 'selesai'
                    ]);

                } elseif ($tipeTransaksi === 'sisa') {
                    $peminjaman->update([
                        'status' => 'sudah dibayar lunas',
                        'sisa_bayar' => 0,
                        'total_dibayarkan' => $peminjaman->dp_dibayarkan + $payment->jumlah,
                    ]);
                } elseif ($tipeTransaksi === 'lunas') {
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

            case 'pending':
                $payment->update(['status' => 'pending']);
                break;

            case 'deny':
            case 'cancel':
            case 'expire':
                $payment->update(['status' => 'failed']);

                // Hapus data peminjaman yang masih menunggu pembayaran (Kecuali jika ini transaksi sisa atau denda)
                if ($peminjaman->status === 'menunggu pembayaran' && in_array($tipeTransaksi, ['dp', 'lunas'])) {
                    $peminjaman->delete();
                    Log::info("Peminjaman {$peminjaman->id} dihapus karena pembayaran awal gagal/batal.");
                }
                break;
        }

        return response()->json(['message' => 'Notification processed']);
    }

    public function cancelPayment(Peminjaman $peminjaman)
    {
        try {
            // Hapus payment transaction yang masih pending
            TransaksiPembayaran::where('peminjaman_id', $peminjaman->id) 
                ->where('status', 'pending')
                ->delete();

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

        $payment = TransaksiPembayaran::where('id_transaksi_midtrans', $orderId)->first(); 
        
        if ($payment && $payment->peminjaman) {
            $peminjaman = $payment->peminjaman;
            
            // 🔹 Normalisasi tipe transaksi
            $tipeTransaksi = strtolower(trim($payment->tipe_transaksi));

            $payment->update([
                'status' => $status,
                'respon_midtrans' => json_encode($data), 
            ]);

            if ($status === 'settlement') {
                if ($tipeTransaksi === 'dp') {
                    $peminjaman->update([
                        'status' => 'pembayaran dp',
                        'dp_dibayarkan' => $payment->jumlah,        
                        'total_dibayarkan' => $payment->jumlah,     
                    ]);
                } elseif ($tipeTransaksi === 'denda') {
                    // Update Tabel Pengembalian
                    $pengembalian = Pengembalian::where('peminjaman_id', $peminjaman->id)->first();
                    if ($pengembalian) {
                        // Status disesuaikan persis seperti permintaan ENUM Anda
                        $pengembalian->status = 'sudah di cek dan denda dibayarkan';
                        $pengembalian->save();
                    }

                    // Update Tabel Denda (Bypass $fillable)
                    $denda = Denda::where('peminjaman_id', $peminjaman->id)
                        ->where('status', 'belum dibayar')
                        ->first();
                    
                    if ($denda) {
                        $denda->status = 'sudah dibayar';
                        $denda->tanggal_pembayaran = now();
                        $denda->metode_pembayaran = 'midtrans';
                        $denda->save();
                    }

                    // Kunci status peminjaman agar TETAP "selesai"
                    $peminjaman->update([
                        'status' => 'selesai'
                    ]);

                } elseif ($tipeTransaksi === 'sisa') {
                    $peminjaman->update([
                        'status' => 'sudah dibayar lunas',
                        'sisa_bayar' => 0,
                        'total_dibayarkan' => $peminjaman->dp_dibayarkan + $payment->jumlah, 
                    ]);
                } elseif ($tipeTransaksi === 'lunas') { 
                    $peminjaman->update([
                        'status' => 'sudah dibayar lunas',
                        'dp_dibayarkan' => $peminjaman->total_harga,
                        'sisa_bayar' => 0,
                        'total_dibayarkan' => $peminjaman->total_harga,
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Callback processed', 'status' => $status]);
    }

    // 🔹 Redirect URLs untuk Frontend
    public function success(Request $request) 
    { 
        $payment = TransaksiPembayaran::with(['peminjaman', 'peminjaman.mobil']) 
            ->whereHas('peminjaman', function($query) {
                $query->where('user_id', Auth::id()); 
            })
            ->latest('created_at') 
            ->first(); 

        return view('user.pesanan.success-payment', compact('payment')); 
    }

    public function failed() { return view('user.pesanan.failed-payment'); }
    public function unfinish() { return view('user.pesanan.unfinish-payment'); }
}