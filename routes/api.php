<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\MidtransController;
use App\Http\Controllers\Admin\MidtransController as AdminMidtransController;
use App\Http\Controllers\Resepsionis\MidtransController as ResepsionisMidtransController;

Route::post('/payment/notification', [MidtransController::class, 'notification'])->name('payment.notification');
// Route::post('/payment/callback', [MidtransController::class, 'callback'])->name('payment.callback');

Route::get('/payment/success', [MidtransController::class, 'success'])->name('payment.success');
Route::get('/payment/failed', [MidtransController::class, 'failed'])->name('payment.failed');
Route::get('/payment/unfinish', [MidtransController::class, 'unfinish'])->name('payment.unfinish');
Route::post('/midtrans/callback', [ResepsionisMidtransController::class, 'callback'])
    ->name('midtrans.callback');

    

