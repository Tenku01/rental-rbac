<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Role-role utama sesuai struktur controller Anda
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'pelanggan']);
        Role::create(['name' => 'resepsionis']);
        Role::create(['name' => 'sopir']);
        Role::create(['name' => 'staff']);
    }
}
