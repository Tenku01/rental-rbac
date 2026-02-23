<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Pelanggan;
use App\Models\Sopir;
use App\Models\Resepsionis;
use App\Models\Staff;

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
        // Jika nama di User berubah atau role baru diset, update data di tabel profil
        
        switch ($role) {
            case 'pelanggan':
                Pelanggan::updateOrCreate(
                    ['user_id' => $user->id],
                    ['nama' => $user->name, 'status' => 'aktif']
                );
                // Hapus profil lain jika user pindah role (Cleanup)
                $this->cleanupProfiles($user, 'pelanggan');
                break;

            case 'resepsionis':
                Resepsionis::updateOrCreate(
                    ['user_id' => $user->id],
                    ['nama' => $user->name, 'status' => 'aktif'] // Default aktif
                );
                $this->cleanupProfiles($user, 'resepsionis');
                break;

            case 'sopir':
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
                if ($sopir->wasRecentlyCreated) {
                    $sopir->update(['status' => 'Tersedia']);
                }
                
                $this->cleanupProfiles($user, 'sopir');
                break;

            case 'staff':
                Staff::updateOrCreate(
                    ['user_id' => $user->id],
                    ['nama' => $user->name, 'status' => 'aktif']
                );
                $this->cleanupProfiles($user, 'staff');
                break;
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Hapus semua data profil terkait
        Pelanggan::where('user_id', $user->id)->delete();
        Sopir::where('user_id', $user->id)->delete();
        Resepsionis::where('user_id', $user->id)->delete();
        Staff::where('user_id', $user->id)->delete();
    }

    /**
     * Helper untuk menghapus profil yang tidak sesuai dengan role saat ini.
     * Mencegah data sampah (misal: User 'Sopir' tapi masih punya data di tabel 'Staff')
     */
    private function cleanupProfiles(User $user, string $currentRole): void
    {
        if ($currentRole !== 'pelanggan') Pelanggan::where('user_id', $user->id)->delete();
        if ($currentRole !== 'resepsionis') Resepsionis::where('user_id', $user->id)->delete();
        if ($currentRole !== 'sopir') Sopir::where('user_id', $user->id)->delete();
        if ($currentRole !== 'staff') Staff::where('user_id', $user->id)->delete();
    }
}