<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Peminjaman;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CancelUnpaidBookings extends Command
{
    /**
     * Nama perintah yang akan dipanggil di terminal atau scheduler
     *
     * @var string
     */
    protected $signature = 'booking:cancel-unpaid';

    /**
     * Deskripsi singkat dari perintah ini
     *
     * @var string
     */
    protected $description = 'Membatalkan otomatis transaksi peminjaman yang belum dibayar lebih dari 1 jam';

    /**
     * Eksekusi logika perintah
     */
    public function handle()
    {
        // 1. Tentukan batas waktu (1 Jam mundur dari waktu sekarang)
        $timeLimit = Carbon::now()->subHour();

        // 2. Cari transaksi yang masih 'menunggu pembayaran' dan usianya sudah lebih dari 1 jam
        $expiredBookings = Peminjaman::where('status', 'menunggu pembayaran')
            ->where('created_at', '<', $timeLimit)
            ->get();

        $count = 0;

        foreach ($expiredBookings as $booking) {
            // 3. Ubah statusnya menjadi dibatalkan
            $booking->update([
                'status' => 'dibatalkan',
            ]);

            // Opsional: Jika di sistem Anda mobil sempat di-lock saat "menunggu pembayaran",
            // Anda bisa membuka kembali lock-nya di sini:
            // $booking->mobil()->update(['status' => 'tersedia']);

            $count++;
        }

        // 4. Tampilkan pesan sukses di terminal (berguna saat Anda test manual)
        $this->info("Selesai! {$count} transaksi berhasil dibatalkan karena expired.");

        // 5. Simpan catatan ke file storage/logs/laravel.log agar Anda punya rekam jejak
        if ($count > 0) {
            Log::info("CRON JOB: Berhasil membatalkan {$count} transaksi yang expired (Menunggu Pembayaran > 1 Jam).");
        }
    }
}