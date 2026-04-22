<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Sopir;
// Model Pelanggan, Resepsionis, dan Staff dihapus karena tabelnya sudah dilebur ke users

class UserObserver
{
    /**
     * Handle the User "saved" event.
     * Menggunakan 'saved' agar menangkap kejadian 'created' DAN 'updated'
     * serta saat $user->touch() dipanggil setelah assignRole.
     */
    public function saved(User $user): void
    {
        // Cek Role Spatie (Mengambil role pertama yang aktif)
        $role = $user->getRoleNames()->first();

        // Jika belum ada role (misal baru create user mentahan), skip dulu
        if (!$role) return;

        // --- SINKRONISASI DATA ---
        // Karena Pelanggan, Resepsionis, dan Staff sudah gabung di tabel users,
        // kita HANYA perlu membuatkan relasi otomatis untuk role Sopir.
        
        if ($role === 'sopir') {
            Sopir::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama' => $user->name, 
                    // Jangan override status jika sudah ada (agar tidak mereset status 'Bekerja')
                    // 'status' => 'Tersedia' 
                ]
            );
            
            // Set default status hanya jika record baru dibuat
            $sopir = Sopir::where('user_id', $user->id)->first();
            if ($sopir && $sopir->wasRecentlyCreated) {
                $sopir->update(['status' => 'Tersedia']);
            }
        }
        
        // Panggil fungsi cleanup untuk memastikan tidak ada data sopir nyangkut
        // jika suatu saat admin mengubah role user dari Sopir menjadi role lain.
        $this->cleanupProfiles($user, $role);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Hapus data profil sopir terkait jika usernya dihapus
        Sopir::where('user_id', $user->id)->delete();
    }

    /**
     * Helper untuk menghapus profil yang tidak sesuai dengan role saat ini.
     * Mencegah data sampah (misal: Admin mengubah role user dari 'Sopir' ke 'Pelanggan', 
     * maka data di tabel sopirs harus dihapus)
     */
    private function cleanupProfiles(User $user, string $currentRole): void
    {
        if ($currentRole !== 'sopir') {
            Sopir::where('user_id', $user->id)->delete();
        }
    }
}