<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SidebarMenuPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar lengkap hak akses khusus untuk visibilitas Menu Sidebar
        $menuPermissions = [
            // Panel Khusus
            'menu_panel_status_sopir',
            
            // Menu Utama
            'menu_dashboard',
            'menu_dashboard_sopir',
            'menu_tugas_aktif',

            // Armada
            'menu_mobil',

            // Pelanggan
            'menu_hak_akses',
            'menu_pengguna',
            'menu_pelanggan',
            'menu_verifikasi_ktp',

            // Penyewaan
            'menu_peminjaman',
            'menu_pengembalian',
            'menu_pembatalan',
            'menu_pembayaran',

            // Operasional
            'menu_inspeksi',
            'menu_laporan_kerusakan',
            'menu_logbook_sopir',  // Logbook untuk tampilan sopir (pribadi)
            'menu_logbook_admin',  // Logbook untuk tampilan admin (semua data)
            'menu_sanksi_denda',

            // Manajemen SDM
            'menu_resepsionis',
            'menu_daftar_sopir',
            'menu_tim_staff',
        ];

        // Buat permissions di database
        foreach ($menuPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}