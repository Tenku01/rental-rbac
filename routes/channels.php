<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Peminjaman;

Broadcast::channel('chat.{peminjaman_id}', function ($user, $peminjaman_id) {

    $peminjaman = Peminjaman::with('sopir')->find($peminjaman_id);

    if (!$peminjaman) {
        return false;
    }

    $isPelanggan = (int) $user->id === (int) $peminjaman->user_id;

    $isSopir = $peminjaman->sopir 
        ? (int) $user->id === (int) $peminjaman->sopir->user_id
        : false;

    return $isPelanggan || $isSopir;
});

Broadcast::channel('admin.guest-chat', function ($user) {
    return $user->hasAnyRole(['admin', 'resepsionis']);
});
