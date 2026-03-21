<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RealUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Affan Rifai (Owner)',
                'email' => 'admin@tokoroni.com',
                'password' => Hash::make('password'),
                'role' => 'owner',
                'jenis_toko' => 'grosir',
                'phone' => '081234567890',
                'address' => 'Indramayu, Jawa Barat',
                'is_active' => true,
            ],
            [
                'name' => 'Siti Aminah (Kasir)',
                'email' => 'kasir@tokoroni.com',
                'password' => Hash::make('password'),
                'role' => 'kasir',
                'jenis_toko' => 'grosir',
                'phone' => '082134567891',
                'address' => 'Juntinyuat, Indramayu',
                'is_active' => true,
            ],
            [
                'name' => 'Budi Santoso (Gudang)',
                'email' => 'gudang@tokoroni.com',
                'password' => Hash::make('password'),
                'role' => 'gudang',
                'jenis_toko' => 'grosir',
                'phone' => '083134567892',
                'address' => 'Karangampel, Indramayu',
                'is_active' => true,
            ],
            [
                'name' => 'Hendra Wijaya (Logistik)',
                'email' => 'logistik@tokoroni.com',
                'password' => Hash::make('password'),
                'role' => 'logistik',
                'jenis_toko' => 'grosir',
                'phone' => '084134567893',
                'address' => 'Indramayu Kotas',
                'is_active' => true,
            ],
            [
                'name' => 'Agus Setiawan (Checker)',
                'email' => 'checker@tokoroni.com',
                'password' => Hash::make('password'),
                'role' => 'checker_barang',
                'jenis_toko' => 'grosir',
                'phone' => '085134567894',
                'address' => 'Jatibarang, Indramayu',
                'is_active' => true,
            ],
            [
                'name' => 'Randi Kurir (Motor)',
                'email' => 'kurir@tokoroni.com',
                'password' => Hash::make('password'),
                'role' => 'kurir',
                'jenis_toko' => 'grosir',
                'phone' => '086134567895',
                'address' => 'Sliyeg, Indramayu',
                'is_active' => true,
            ],
            [
                'name' => 'Dedi Driver (Truck)',
                'email' => 'driver@tokoroni.com',
                'password' => Hash::make('password'),
                'role' => 'driver',
                'jenis_toko' => 'grosir',
                'phone' => '087134567896',
                'address' => 'Widasari, Indramayu',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
