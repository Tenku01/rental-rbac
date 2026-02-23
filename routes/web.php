<?php

use App\Models\Resepsionis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Sopir\LogbookController;
use App\Http\Controllers\User\IdentityController;
use App\Http\Controllers\User\MidtransController;
use App\Http\Controllers\User\MobilControllerUser;
use App\Http\Controllers\User\PeminjamanController;
use App\Http\Controllers\Resepsionis\FineController;
use App\Http\Controllers\Resepsionis\UserController;
use App\Http\Controllers\Sopir\SopirActionController;
use App\Http\Controllers\User\PengembalianController;
use App\Http\Controllers\Sopir\SopirDashboardController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Resepsionis\DashboardController;
use App\Http\Controllers\Resepsionis\PelangganController;
use App\Http\Controllers\User\PembatalanPesananController;
use App\Http\Controllers\Resepsionis\TransactionController;
use App\Http\Controllers\Resepsionis\VerificationController;
use App\Http\Controllers\Admin\PembatalanPesananApprovalController;
use App\Http\Controllers\Resepsionis\MobilController as ResepsionisMobilController;
use App\Http\Controllers\Resepsionis\MidtransController as ResepsionisMidtransController;
use App\Http\Controllers\Resepsionis\PeminjamanController as ResepsionisPeminjamanController;
use App\Http\Controllers\Resepsionis\PengembalianController as ResepsionisPengembalianController;
use App\Http\Controllers\Resepsionis\PembatalanPesananController as ResepsionisPembatalanPesananController;


use App\Livewire\Menu\Dashboard\HomeIndex;
use App\Livewire\Menu\Master\{
    UserIndex,
    RoleIndex,
    MobilIndex,
    SopirIndex,
    StaffIndex,
    ResepsionisIndex,
    PelangganIndex
  
};
use App\Livewire\Menu\Transaksi\{
    PeminjamanIndex,
    PengembalianIndex,
    PembatalanPesananIndex,
    TransactionIndex,
    FineIndex,
    PembayaranIndex
};
use App\Livewire\Menu\Operasional\{
    DriverLogbookIndex,
    VehicleInspectionIndex,
    VehicleDamageIndex,
    VerifikasiUserIndex
};
use App\Livewire\Sopir\Dashboard as SopirDashboard;
use App\Livewire\Sopir\TugasAktif;
use App\Livewire\Staff\Dashboard as StaffDashboard;

// Landing page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Auth routes (login, register, forgot password, dll)
require __DIR__ . '/auth.php';

// Hanya untuk user yang login
Route::middleware(['auth','verified'])->group(function () {

    // Dashboard user
    Route::get('/dashboard', function () {
        return view('user.dashboard');
    })->name('dashboard');
     // --- PANEL KHUSUS SOPIR ---
    Route::middleware('role:sopir')->group(function () {
        Route::get('/sopir/dashboard', SopirDashboard::class)->name('sopir.dashboard');
        // Route baru untuk SPA Logbook & Tugas Aktif Sopir
        Route::get('/sopir/tugas-aktif', TugasAktif::class)->name('sopir.activeTasks');
    });

Route::middleware('role:staff')->group(function () {
        Route::get('/staff/dashboard', StaffDashboard::class)->name('staff.dashboard');
    });

    // Mobil (User)
    Route::get('/mobils', [MobilControllerUser::class, 'mobil'])->name('mobils.index');
    Route::get('/mobils/{id}', [MobilControllerUser::class, 'show'])->name('mobils.show');

    // Identitas / Profil
    Route::get('user/upload-identity', [IdentityController::class, 'showUploadForm'])->name('upload.identity');
    Route::post('user/upload-identity', [IdentityController::class, 'store']);
    Route::get('user/edit-identity/{id}', [IdentityController::class, 'edit'])->name('edit.identity');
    Route::post('user/edit-identity/{id}', [IdentityController::class, 'update'])->name('update.identity');
    Route::delete('user/delete-identity/{id}', [IdentityController::class, 'destroy'])->name('delete.identity');

    // Pesanan & Peminjaman
    // Route::get('/pesanan-saya', [PeminjamanController::class, 'pesananSaya'])->name('pesanan.index');
    Route::get('/peminjaman/{mobil_id}', [PeminjamanController::class, 'create'])->name('peminjaman.create');
    Route::post('/peminjaman/store', [PeminjamanController::class, 'store'])->name('peminjaman.store');
    Route::post('/check-driver-availability', [PeminjamanController::class, 'checkDriver'])
        ->name('check.driver');
    // Midtrans Payment Integration
    Route::get('/pesanan-saya', [PeminjamanController::class, 'index'])->name('pesanan.saya');

    // Cek kondisi mobil sebelum pengembalian
    Route::post('/pesanan/{peminjaman}/cek-kondisi', [PeminjamanController::class, 'cekKondisi'])->name('peminjaman.cek-kondisi');


    // 🔹 Route Midtrans (sudah ada)
    Route::get('/peminjaman/{peminjaman}/pay', [MidtransController::class, 'pay'])->name('payment.pay');
    Route::get('/peminjaman/{peminjaman}/pay-sisa', [MidtransController::class, 'paySisa'])->name('payment.pay-sisa');
    Route::post('/payment/notification', [MidtransController::class, 'notification'])->name('payment.notification');
    Route::get('/payment/callback', [MidtransController::class, 'callback'])->name('payment.callback');
    Route::get('/payment/success', [MidtransController::class, 'success'])->name('payment.success');
    Route::get('/payment/failed', [MidtransController::class, 'failed'])->name('payment.failed');
    Route::get('/payment/unfinish', [MidtransController::class, 'unfinish'])->name('payment.unfinish');
    // 🔹 Batalkan transaksi jika Snap ditutup tanpa pembayaran
    Route::delete('/peminjaman/{peminjaman}/cancel-payment', [MidtransController::class, 'cancelPayment'])
        ->name('peminjaman.cancel-payment');
    Route::delete('/peminjaman/{id}/cancel', [PeminjamanController::class, 'cancel'])
        ->name('peminjaman.cancel');

    //pengembalian
    // Route::post('/peminjaman/{id}/pengembalian', [PeminjamanController::class, 'prosesPengembalian'])
    //     ->name('peminjaman.pengembalian');
    // Route::post('/pengembalian/{peminjaman}', [PengembalianController::class, 'store'])->name('pengembalian.store');
    Route::post('/pengembalian/pengecekan/{id}', [PengembalianController::class, 'pengecekan'])->name('pengembalian.pengecekan');
    Route::post('/pengembalian/bayar/{id}', [PengembalianController::class, 'bayarDenda'])->name('pengembalian.bayar');
    Route::post('/peminjaman/{peminjaman}/cancel', [PembatalanPesananController::class, 'store'])
        ->name('pembatalan.store');

    Route::post('/peminjaman/{peminjaman_id}/kembalikan', [PengembalianController::class, 'store'])
        ->name('pengembalian.store');

    // Melihat Status & Detail Pengembalian (Step 5-6)
    Route::get('/pengembalian/{kode_pengembalian}', [PengembalianController::class, 'show'])
        ->name('pengembalian.show');

    // Inisiasi Pembayaran Midtrans (Step 7-8)
    Route::post('/pengembalian/{kode_pengembalian}/snap-token', [PengembalianController::class, 'generateSnapToken'])
        ->name('pengembalian.generateSnapToken');

    // Memilih Metode Pembayaran Manual/Tunai (Step 7, Aksi Tunai/Transfer)
    Route::post('/pengembalian/{kode_pengembalian}/select-manual-payment', [PengembalianController::class, 'selectManualPaymentMethod'])
        ->name('pengembalian.selectManualPaymentMethod');

    // // Route dari definisi lama Anda (jika masih diperlukan)
    // Route::post('/peminjaman/{peminjaman}/cancel', [PembatalanPesananController::class, 'store'])
    //     ->name('pembatalan.store');

    //Profile
    Route::view('profile', 'profile')
        ->middleware(['auth'])
        ->name('profile');
    // Logout
    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/');
    })->name('logout');

    // Dashboard Admin & Manajemen Akses
    Route::get('/home', HomeIndex::class)->name('home');
});
     // --- GROUP MASTER DATA ---
    Route::middleware('permission:read-users')->get('/management/users', UserIndex::class)->name('users');
        Route::middleware('permission:read-pelanggan')->get('/management/pelanggan', PelangganIndex::class)->name('pelanggan');
    Route::middleware('permission:read-roles')->get('/management/roles', RoleIndex::class)->name('roles');
    Route::middleware('permission:read-mobils')->get('/management/mobil', MobilIndex::class)->name('mobil');
    Route::middleware('permission:read-sopirs')->get('/management/sopir', SopirIndex::class)->name('sopir');
    Route::middleware('permission:read-staffs')->get('/management/staff', StaffIndex::class)->name('staff');
    Route::middleware('permission:read-resepsionis')->get('/management/resepsionis', ResepsionisIndex::class)->name('resepsionis');
    Route::middleware('permission:read-user_identifications')->get('/management/verifikasi', VerifikasiUserIndex::class)->name('verifikasi');

    // --- GROUP TRANSAKSI ---
    Route::middleware('permission:read-peminjaman')->get('/transaksi/peminjaman', PeminjamanIndex::class)->name('peminjaman');
    Route::middleware('permission:read-pengembalian')->get('/transaksi/pengembalian', PengembalianIndex::class)->name('pengembalian');
    Route::middleware('permission:read-pembatalan_pesanan')->get('/transaksi/pembatalan', PembatalanPesananIndex::class)->name('pembatalan');
    Route::middleware('permission:read-payment_transactions')->get('/transaksi/payments', PembayaranIndex::class)->name('pembayaran');

    // --- GROUP OPERASIONAL ---
    Route::middleware('permission:read-driver_logbooks')->get('/operasional/logbook', DriverLogbookIndex::class)->name('logbook');
    Route::middleware('permission:read-vehicle_inspections')->get('/operasional/inspeksi', VehicleInspectionIndex::class)->name('inspeksi');
    Route::middleware('permission:read-vehicle_damage_reports')->get('/operasional/laporan-kerusakan', VehicleDamageIndex::class)->name('damage-report');
    Route::middleware('permission:read-fines')->get('/operasional/denda', FineIndex::class)->name('fines');




   
   