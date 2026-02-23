<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Pengembalian;
use App\Models\Staff;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    #[Layout('layouts.admin')] // Menggunakan layout utama yang ada sidebarnya
    public function render()
    {
        // PROTEKSI HAK AKSES: Hanya Staff yang bisa mengakses (berdasarkan relasi tabel staffs)
        $isStaff = Staff::where('user_id', Auth::id())->exists();
        abort_unless($isStaff, 403, 'Akses ditolak. Halaman khusus Staff Operasional.');

        // 1. Ambil data Metrik (Sesuai Controller Lama)
        $totalCompleted = Pengembalian::whereIn('status', ['selesai', 'selesai pengecekan'])->count();
        $needsReview = Pengembalian::where('status', 'menunggu pengecekan')->count();

        $metrics = [
            [
                'label' => 'Perlu Cek',
                'value' => $needsReview,
                'color' => 'yellow',
                'icon_path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>'
            ],
            [
                'label' => 'Total Selesai',
                'value' => $totalCompleted,
                'color' => 'green',
                'icon_path' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>'
            ]
        ];

        // 2. Ambil 5 Antrean Pengecekan Terakhir
        $latestChecks = Pengembalian::with(['peminjaman.user', 'peminjaman.mobil'])
            ->orderByRaw("FIELD(status, 'menunggu pengecekan') DESC") // Prioritaskan yang menunggu
            ->orderBy('tanggal_pengembalian', 'desc')
            ->take(5)
            ->get();

        return view('livewire.staff.dashboard', [
            'metrics' => $metrics,
            'latestChecks' => $latestChecks,
        ]);
    }
}