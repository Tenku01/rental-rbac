<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mengambil data notifikasi untuk Navbar Pelanggan
     */
    public function getNotifications()
    {
        // 1. Cari semua pesanan user yang sedang aktif
        $activeChats = Peminjaman::where('user_id', Auth::id())
            ->whereIn('status', ['pembayaran dp', 'sudah dibayar lunas', 'berlangsung'])
            ->pluck('id');
        
        // 2. Ambil detail pesan yang belum dibaca dari database
        $unreadMessagesDB = Message::whereIn('peminjaman_id', $activeChats)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Format pesan agar siap ditampilkan di Alpine.js (Dropdown Navbar)
        $formattedMessages = $unreadMessagesDB->take(10)->map(function($msg) {
            return [
                'id' => $msg->peminjaman_id,
                'sender' => 'Sopir / Admin',
                'title' => 'Pesanan #' . $msg->peminjaman_id,
                'text' => $msg->message,
                'time' => $msg->created_at->diffForHumans()
            ];
        })->values();

        // Kembalikan sebagai JSON
        return response()->json([
            'chatIds' => $activeChats,
            'unreadCount' => $unreadMessagesDB->count(),
            'messages' => $formattedMessages
        ]);
    }

    /**
     * Menandai semua pesan yang ada di Navbar sebagai sudah dibaca
     */
    public function markAsRead()
    {
        $activeChats = Peminjaman::where('user_id', Auth::id())
            ->whereIn('status', ['pembayaran dp', 'sudah dibayar lunas', 'berlangsung'])
            ->pluck('id');

        Message::whereIn('peminjaman_id', $activeChats)
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}