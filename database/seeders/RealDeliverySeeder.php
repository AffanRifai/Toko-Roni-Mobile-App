<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Hash;

class RealDeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Vehicles
        $vehicles = [
            [
                'name' => 'Honda Vario 150',
                'type' => 'Motorcycle',
                'license_plate' => 'B 1234 ABC',
                'capacity_weight' => 50,
                'capacity_volume' => 0.2,
                'status' => 'available',
                'last_maintenance' => now()->subMonths(1),
            ],
            [
                'name' => 'Honda Supra X 125',
                'type' => 'Motorcycle',
                'license_plate' => 'B 5678 DEF',
                'capacity_weight' => 40,
                'capacity_volume' => 0.15,
                'status' => 'available',
                'last_maintenance' => now()->subMonths(2),
            ],
            [
                'name' => 'Toyota Hilux Pickup',
                'type' => 'Pickup',
                'license_plate' => 'B 9012 GHI',
                'capacity_weight' => 1000,
                'capacity_volume' => 2.5,
                'status' => 'available',
                'last_maintenance' => now()->subMonths(3),
            ],
            [
                'name' => 'Daihatsu Gran Max Van',
                'type' => 'Van',
                'license_plate' => 'B 3456 JKL',
                'capacity_weight' => 800,
                'capacity_volume' => 3.5,
                'status' => 'available',
                'last_maintenance' => now()->subMonths(1),
            ],
        ];

        foreach ($vehicles as $v) {
            Vehicle::updateOrCreate(['license_plate' => $v['license_plate']], $v);
        }

        // 2. Create Couriers
        $couriers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.kurir@tokoroni.com',
                'password' => Hash::make('password'),
                'role' => 'kurir',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No. 10, Jakarta',
                'status' => 'active',
                'is_active' => true,
                'delivery_status' => 'active',
                'delivery_rating' => 4.8,
                'delivery_rating_count' => 150,
            ],
            [
                'name' => 'Agus Setiawan',
                'email' => 'agus.kurir@tokoroni.com',
                'password' => Hash::make('password'),
                'role' => 'kurir',
                'phone' => '082345678901',
                'address' => 'Jl. Sudirman No. 25, Jakarta',
                'status' => 'active',
                'is_active' => true,
                'delivery_status' => 'active',
                'delivery_rating' => 4.7,
                'delivery_rating_count' => 120,
            ],
            [
                'name' => 'Rina Wijaya',
                'email' => 'rina.kurir@tokoroni.com',
                'password' => Hash::make('password'),
                'role' => 'kurir',
                'phone' => '083456789012',
                'address' => 'Jl. Thamrin No. 5, Jakarta',
                'status' => 'active',
                'is_active' => true,
                'delivery_status' => 'active',
                'delivery_rating' => 4.9,
                'delivery_rating_count' => 200,
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti.kurir@tokoroni.com',
                'password' => Hash::make('password'),
                'role' => 'kurir',
                'phone' => '084567890123',
                'address' => 'Jl. Gatot Subroto No. 40, Jakarta',
                'status' => 'active',
                'is_active' => true,
                'delivery_status' => 'active',
                'delivery_rating' => 4.6,
                'delivery_rating_count' => 95,
            ],
        ];

        foreach ($couriers as $c) {
            User::updateOrCreate(['email' => $c['email']], $c);
        }
    }
}
