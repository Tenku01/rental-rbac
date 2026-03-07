<?php

namespace App\Http\Controllers\User;

use App\Models\Message;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Mobil;
use App\Models\Peminjaman;
use App\Models\PaymentTransaction;
use App\Models\Sopir; // Pastikan Model Sopir diimport
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Midtrans\Snap;
use Midtrans\Config;
use App\Jobs\AssignDriver; 
use midtrans\Transaction;
use midtrans\Notification;

class PeminjamanController extends Controller
{
    public function checkDriver(Request $request)
    {
        // Log input untuk debugging
        Log::info('checkDriver called', [
            'tanggal_sewa' => $request->input('tanggal_sewa'),
            'tanggal_kembali' => $request->input('tanggal_kembali'),
            'all_inputs' => $request->all(),
        ]);
        
        // 1. Validasi Input
        if (!$request->filled('tanggal_sewa') || !$request->filled('tanggal_kembali')) {
            return response()->json([
                'available' => false,
                'message' => 'Tanggal input tidak lengkap',
                'debug' => [
                    'tanggal_sewa' => $request->input('tanggal_sewa'),
                    'tanggal_kembali' => $request->input('tanggal_kembali'),
                ]
            ]);
        }

        try {
            // Konversi input ke format Y-m-d
            $start = Carbon::createFromFormat('d-m-Y', $request->tanggal_sewa)->format('Y-m-d');
            $end   = Carbon::createFromFormat('d-m-Y', $request->tanggal_kembali)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'available' => false,
                'message' => 'Format tanggal invalid: ' . $e->getMessage()
            ], 400);
        }

        // 2. Cari ID sopir yang bentrok (sudah ada peminjaman di rentang tanggal)
        $bookedSopirIds = Peminjaman::whereNotNull('sopir_id')
            ->whereIn('status', ['menunggu pembayaran','pembayaran dp','sudah dibayar lunas','berlangsung'])
            ->where(function($q) use ($start, $end) {
                $q->where('tanggal_sewa', '<=', $end)
                ->where('tanggal_kembali', '>=', $start);
            })
            ->pluck('sopir_id')
            ->toArray();

        // 3. Ambil sopir yang tersedia (status 'tersedia' atau 'bekerja') dan tidak bentrok
        $availableSopirs = Sopir::whereIn('status', ['tersedia', 'bekerja'])
            ->when(!empty($bookedSopirIds), function($q) use ($bookedSopirIds) {
                $q->whereNotIn('id', $bookedSopirIds);
            })
            ->get(['id','nama','status']);

        // 4. Debug: Ambil semua sopir untuk cek status
        $allSopirs = Sopir::get(['id', 'nama', 'status']);
        $sopirByStatus = $allSopirs->groupBy('status');

        // 5. Return JSON
        $response = [
            'available'   => $availableSopirs->count() > 0,
            'sisa_sopir'  => $availableSopirs->count(),
            'sopirs'      => $availableSopirs->pluck('nama'),
            'debug'       => [
                'input_tanggal_sewa' => $request->tanggal_sewa,
                'input_tanggal_kembali' => $request->tanggal_kembali,
                'parsed_start' => $start,
                'parsed_end' => $end,
                'ids_sopir_bentrok' => $bookedSopirIds,
                'total_sopir_db' => Sopir::count(),
                'all_sopirs' => $allSopirs,
                'sopir_by_status' => $sopirByStatus,
                'searched_status' => ['tersedia', 'bekerja'],
                'available_sopirs' => $availableSopirs,
            ]
        ];
        
        Log::info('checkDriver response', $response);
        
        return response()->json($response);
    }



    public function show($id)
    {
        // Ambil data peminjaman berdasarkan ID
        $peminjaman = Peminjaman::findOrFail($id); 
    
        // Kirim data ke tampilan
        return view('user.pesanan.pesanan', compact('peminjaman'));
    }
    
    public function index()
    {
        $peminjaman = Peminjaman::with([
                'mobil',
                'paymentTransactions',
                'pembatalan', // ⬅︎ penting: agar UI bisa baca approval_status tanpa N+1
            ])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
    
        return view('user.pesanan.pesanan', compact('peminjaman'));
    }

    /**
     * 🔹 Simulasikan Midtrans Notification Handler untuk konfirmasi pembayaran sukses.
     */
    public function handlePaymentSuccess(Request $request)
    {
        $peminjamanId = $request->input('peminjaman_id');
        $tipeTransaksi = $request->input('tipe_transaksi'); 
        
        $peminjaman = Peminjaman::find($peminjamanId);

        if (!$peminjaman) {
            return response()->json(['error' => 'Peminjaman tidak ditemukan'], 404);
        }

        // Logika update status peminjaman
        if ($tipeTransaksi === 'lunas' || $tipeTransaksi === 'sisa') {
            $peminjaman->status = 'sudah dibayar lunas';
            $peminjaman->total_dibayarkan = $peminjaman->total_harga; 
        } elseif ($tipeTransaksi === 'dp') {
            $peminjaman->status = 'pembayaran dp';
        }
        
        $peminjaman->save();

        // --- Logic Penugasan Sopir ---
        if ($peminjaman->add_on_sopir) {
            // Pemicu otomatis penugasan sopir setelah pembayaran lunas/DP sukses
            AssignDriver::dispatch($peminjaman);
        }
        // --- End Logic Penugasan Sopir ---

        return response()->json(['message' => 'Status peminjaman diperbarui dan sopir ditugaskan jika diperlukan.'], 200);
    }

    /**
     * 🔹 Cek kondisi mobil saat pengembalian
     */
   public function cekKondisi(Request $request, Peminjaman $peminjaman)
    {
        try {
            $request->validate([
                'kondisi' => 'nullable|string', 
            ]);

            if ($peminjaman->status !== 'sudah dibayar lunas') {
                return response()->json(['error' => 'Mobil belum dilunasi, tidak bisa melakukan pengecekan kondisi.'], 400);
            }
            
            $existingKondisi = $peminjaman->kondisi_mobil ?? '';
            $newKondisi = $existingKondisi;
            if ($request->kondisi) {
                $newKondisi = $existingKondisi ? $existingKondisi . ' dan ' . $request->kondisi : $request->kondisi;
            }

            $mobil = $peminjaman->mobil;
            $mobil->update(['status' => 'tersedia']); 

            $peminjaman->update([
                'status' => 'berlangsung',
                'kondisi_mobil' => $newKondisi, 
                'tanggal_pengembalian' => Carbon::now(), 
            ]);

            return response()->json(['success' => '✅ Kondisi mobil berhasil dikonfirmasi dan status diperbarui menjadi Berlangsung.']);
        } catch (\Exception $e) {
            Log::error('Cek kondisi gagal: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat memproses pengecekan kondisi.'], 500);
        }
    }
    
    /**
     * 🔹 Menampilkan form peminjaman
     */
     public function create($mobil_id)
    {
        $mobil = Mobil::findOrFail($mobil_id);

        $hasIdentification = DB::table('user_identifications')
            ->where('user_id', Auth::id())
            ->exists();

        if (!$hasIdentification) {
            return redirect()->back()
                ->with('warning', '⚠ Identitas belum dilengkapi. Silakan unggah identitas Anda terlebih dahulu sebelum melakukan peminjaman.');
        }

        // Catatan: $isDriverAvailable di sini hanya cek status statis 'tersedia' atau 'bekerja' sebagai inisial.
        // Pengecekan real-time berdasarkan tanggal dilakukan via AJAX ke checkDriver.
        $isDriverAvailable = Sopir::whereIn('status', ['tersedia', 'bekerja'])->exists();

        $bookedDates = [];
        foreach ($mobil->peminjaman as $p) {
            $start =Carbon::parse($p->tanggal_sewa);
            $end = Carbon::parse($p->tanggal_kembali);
            for ($date = $start; $date->lte($end); $date->addDay()) {
                $bookedDates[] = $date->format('d-m-Y'); 
            }
        }

        return view('user.pesanan.peminjaman', compact('mobil', 'bookedDates', 'isDriverAvailable'));
    }

    /**
     * 🔹 Menyimpan data peminjaman dan membuat transaksi Midtrans DP
     */
public function store(Request $request)
    {
        $request->validate([
            'mobil_id' => 'required|exists:mobils,id',
            'tanggal_sewa' => 'required|string',
            'jam_sewa' => 'required',
            'tanggal_kembali' => 'required|string|different:tanggal_sewa',
            'add_on_sopir' => 'required|boolean',
            'metode_pembayaran' => 'nullable|string', // Pastikan ini ada
            'tipe_pembayaran' => 'required|in:dp,lunas',
            'dp' => 'nullable|numeric',
        ]);

        $mobil = Mobil::findOrFail($request->mobil_id);
        $tanggalSewa = Carbon::createFromFormat('d-m-Y', $request->tanggal_sewa)->format('Y-m-d');
        $tanggalKembali = Carbon::createFromFormat('d-m-Y', $request->tanggal_kembali)->format('Y-m-d');

        // --- LOGIC ASSIGN SOPIR ---
        $sopirId = null;
        if ((int) $request->add_on_sopir === 1) {
            
            // 1. Cari sopir yang sibuk di tanggal ini
            $sopirBentrokIds = Peminjaman::whereNotNull('sopir_id')
                ->whereIn('status', ['menunggu pembayaran', 'pembayaran dp', 'sudah dibayar lunas', 'berlangsung'])
                ->where(function ($q) use ($tanggalSewa, $tanggalKembali) {
                     $q->where('tanggal_sewa', '<=', $tanggalKembali)
                       ->where('tanggal_kembali', '>=', $tanggalSewa);
                })
                ->pluck('sopir_id')
                ->toArray();

            // 2. Pilih Sopir yang tersedia (tersedia atau bekerja) dan tidak bentrok dengan jadwal
            $sopir = Sopir::whereIn('status', ['tersedia', 'bekerja'])
                ->whereNotIn('id', $sopirBentrokIds)
                ->inRandomOrder()
                ->first();

            if (!$sopir) {
                return response()->json(['error' => 'Maaf, sopir penuh atau tidak tersedia pada tanggal tersebut.'], 422);
            }
            $sopirId = $sopir->id;
        }

        // Cek bentrok mobil
        $conflict = Peminjaman::where('mobil_id', $mobil->id)
            ->whereIn('status', ['menunggu pembayaran', 'pembayaran dp', 'sudah dibayar lunas', 'berlangsung'])
            ->where(function ($query) use ($tanggalSewa, $tanggalKembali) {
                $query->where('tanggal_sewa', '<=', $tanggalKembali)
                      ->where('tanggal_kembali', '>=', $tanggalSewa);
            })
            ->exists();

        if ($conflict) {
            return response()->json(['error' => 'Mobil sudah dibooking pada tanggal yang dipilih.'], 422);
        }

        // Kalkulasi Biaya
        $start = Carbon::parse($tanggalSewa . ' ' . $request->jam_sewa);
        $end = Carbon::parse($tanggalKembali . ' ' . $request->jam_sewa);
        $lama = ceil($start->diffInHours($end) / 24);
        $lama = max($lama, 1);

        $biayaSewa = $lama * $mobil->harga;
        // Koreksi harga sopir sesuai frontend (sebelumnya 1500, ubah ke 150000 agar sesuai standar)
        $biayaSopir = $request->add_on_sopir ? 150000 * $lama : 0; 
        $total = $biayaSewa + $biayaSopir;

        // Payment Logic
        if ($request->tipe_pembayaran === 'dp') {
            $dp = $request->dp ?: ($total * 0.5);
            $sisa = $total - $dp;
            $jumlahBayar = $dp;
            $tipeTransaksi = 'dp';
        } else {
            $dp = 0;
            $sisa = 0;
            $jumlahBayar = $total;
            $tipeTransaksi = 'lunas';
        }

        // Create Peminjaman
        // Status default adalah 'menunggu pembayaran'
        $peminjaman = Peminjaman::create([
            'user_id' => Auth::id(),
            'mobil_id' => $mobil->id,
            'sopir_id' => $sopirId,
            'tanggal_sewa' => $tanggalSewa,
            'jam_sewa' => $request->jam_sewa,
            'tanggal_kembali' => $tanggalKembali,
            'add_on_sopir' => $request->add_on_sopir,
            'total_harga' => $total,
            'dp_dibayarkan' => $dp,
            'sisa_bayar' => $sisa,
            'total_dibayarkan' => $jumlahBayar,
            'status' => 'menunggu pembayaran', 
            'tipe_pembayaran' => $tipeTransaksi,
            'metode_pembayaran' => $request->metode_pembayaran ?? 'transfer',
        ]);

        $mobil->update(['status' => 'disewa']);

        // --- BRANCHING: CASH VS MIDTRANS ---
        
        // Jika metode pembayaran adalah CASH (Bayar di Tempat)
        if ($request->metode_pembayaran === 'cash') {
            // Kita tidak perlu membuat Snap Token.
            // Langsung return success agar frontend bisa redirect.
            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat. Silakan lakukan pembayaran di tempat.',
                'redirect_url' => route('user.pesanan.pesanan'), // Redirect ke halaman list pesanan
                'peminjaman_id' => $peminjaman->id
            ]);
        }

        // Jika metode pembayaran adalah TRANSFER (Midtrans)
        try {
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $orderId = strtoupper($tipeTransaksi) . '-' . $peminjaman->id . '-' . time();
            
            $midtransParams = [
                'transaction_details' => ['order_id' => $orderId, 'gross_amount' => (int)$jumlahBayar],
                'customer_details' => ['first_name' => Auth::user()->name, 'email' => Auth::user()->email],
            ];

            $snapToken = Snap::getSnapToken($midtransParams);

            PaymentTransaction::create([
                'peminjaman_id' => $peminjaman->id,
                'midtrans_transaction_id' => $orderId,
                'status' => 'pending',
                'amount' => $jumlahBayar,
                'tipe_transaksi' => $tipeTransaksi,
                'midtrans_response' => json_encode($midtransParams),
            ]);

            return response()->json([
                'snap_token' => $snapToken,
                'peminjaman_id' => $peminjaman->id
            ]);

        } catch (\Exception $e) {
            $mobil->update(['status' => 'tersedia']);
            $peminjaman->delete();
            return response()->json(['error' => 'Gagal transaksi: ' . $e->getMessage()], 500);
        }
    }
    
    public function cancel($id)
    {
        $peminjaman = Peminjaman::find($id);

        if (!$peminjaman) {
            return response()->json(['error' => 'Data peminjaman tidak ditemukan'], 404);
        }

        // 1. Hapus transaksi Midtrans terkait (Bersihkan PaymentTransaction)
        // Kita hapus semua transaksi milik peminjaman ini agar tidak ada duplikasi/sampah
        PaymentTransaction::where('peminjaman_id', $peminjaman->id)->delete();

        // 2. Ubah status mobil jadi tersedia lagi
        if ($peminjaman->mobil) {
            $peminjaman->mobil->update(['status' => 'tersedia']);
        }

        // 3. Hapus data peminjaman utama
        $peminjaman->delete();

        return response()->json(['message' => 'Peminjaman dibatalkan dan data dihapus']);
    }
    
    
    /**
     * 🔹 Halaman daftar pesanan user
     */
    public function pesananSaya()
    {
        $peminjaman = Peminjaman::where('user_id', Auth::id())
            ->with('mobil')
            ->get();
    
        return view('user.pesanan.pesanan', compact('peminjaman'));
    }
    
    /**
     * 🔹 Proses pelunasan sisa bayar
     */
    public function bayarSisa(Request $request, $id)
    {
        $peminjaman = Peminjaman::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    
        if ($peminjaman->status !== 'pembayaran dp') {
            return response()->json(['error' => 'Peminjaman tidak memerlukan pelunasan.'], 400);
        }
    
        $sisa = $peminjaman->sisa_bayar;
    
        try {
            Config::$serverKey = config('services.midtrans.server_key');
            Config::$isProduction = config('services.midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;
    
            $orderId = 'SEWA-LUNAS-' . $peminjaman->id . '-' . time();
    
            $midtransParams = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $sisa,
                ],
                'customer_details' => [
                    'first_name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ],
                'enabled_payments' => ['shopeepay',
                'qris','bank_transfer','credit_card'], 
                'gopay' => [
                'enable_callback' => true,
            ],
            'qris' => [
                'acquirer' => 'gopay', 
            ], 
            ];
    
            $snapToken = Snap::getSnapToken($midtransParams);
    
            PaymentTransaction::create([
                'peminjaman_id' => $peminjaman->id,
                'midtrans_transaction_id' => $orderId,
                'status' => 'pending',
                'amount' => $sisa,
                'tipe_transaksi' => 'sisa',
                'midtrans_response' => json_encode($midtransParams),
            ]);
    
            return response()->json([
                'snap_token' => $snapToken,
                'peminjaman_id' => $peminjaman->id,
                'sisa' => $sisa,
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => '❌ Gagal membuat transaksi pelunasan: ' . $e->getMessage()
            ], 500);
        }
    }

   public function kirimPesan(Request $request)
    {
        // 1. Validasi input dari frontend
        $request->validate([
            'peminjaman_id' => 'required',
            'message' => 'required|string'
        ]);

        // 2. Simpan pesan ke database
        $message = Message::create([
            'peminjaman_id' => $request->peminjaman_id,
            'sender_id' => Auth::id(), // Menggunakan Auth::id() sesuai permintaan Anda
            'message' => $request->message,
        ]);

        // 3. Triger event Reverb agar langsung dikirim ke Sopir secara real-time
        broadcast(new MessageSent($message));

        // 4. Kembalikan respon sukses ke frontend (Axios)
        return response()->json([
            'status' => 'Pesan terkirim', 
            'data' => $message
        ]);
    }

}