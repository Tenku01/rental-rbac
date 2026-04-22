<?php

namespace App\Http\Controllers;

use App\Events\GuestMessageEvent;
use App\Models\Pesan;
use Illuminate\Http\Request;

class GuestChatController extends Controller
{
    /**
     * Mengambil riwayat chat untuk pengunjung berdasarkan session ID mereka
     */
    public function fetchMessages($sessionId)
    {
        $messages = Pesan::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'session_id' => $msg->session_id,
                    'isi_pesan' => $msg->isi_pesan,
                    'pengirim_id' => $msg->pengirim_id,
                    'waktu' => $msg->created_at->format('H:i'),
                ];
            });

        return response()->json($messages);
    }

    /**
     * Menerima pesan masuk dari pengunjung dan menyimpannya
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'isi_pesan' => 'required|string',
        ]);

        // 1. Simpan pesan ke database
        $pesan = new Pesan();
        $pesan->session_id = $request->session_id;
        $pesan->isi_pesan = $request->isi_pesan;
        
        // Pastikan null karena dikirim oleh Guest (belum login)
        $pesan->pengirim_id = null; 
        $pesan->peminjaman_id = null; 
        
        $pesan->save();

        // 2. Broadcast pesan tersebut menggunakan Reverb
        // Menggunakan toOthers() agar pengirim tidak menerima pesan ganda di layarnya sendiri
        broadcast(new GuestMessageEvent($pesan))->toOthers();

        return response()->json([
            'status' => 'success',
            'pesan' => [
                'id' => $pesan->id,
                'session_id' => $pesan->session_id,
                'isi_pesan' => $pesan->isi_pesan,
                'pengirim_id' => $pesan->pengirim_id,
                'waktu' => $pesan->created_at->format('H:i'),
            ]
        ]);
    }
}