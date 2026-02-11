<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache agar perubahan langsung terdeteksi
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar 16 tabel utama berdasarkan database Anda
        $tables = [
            'users', 'mobils', 'peminjaman', 'pengembalian',
            'pembatalan_pesanan', 'payment_transactions', 'fines', 'pelanggans',
            'sopirs', 'staffs', 'resepsionis', 'driver_logbooks',
            'vehicle_inspections', 'vehicle_damage_reports', 'user_identifications', 'roles'
        ];

        $actions = ['read', 'create', 'update', 'delete'];

        foreach ($tables as $table) {
            foreach ($actions as $action) {
                // Menghasilkan nama seperti: read-users, create-users, dst.
                Permission::findOrCreate("{$action}-{$table}", 'web');
            }
        }

        // Memberikan akses penuh ke Admin (Total 64 Permission)
        $adminRole = Role::findByName('admin');
        $adminRole->syncPermissions(Permission::all());
    }
}