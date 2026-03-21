<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class RealProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Category Mappings (based on existing IDs)
        // [1] => Sembako, [2] => Minuman, [3] => Snack, [4] => Bumbu Dapur, [5] => Peralatan Mandi
        
        $products = [
            // Sembako (1)
            [
                'name' => 'Beras Rojo Lele 5kg',
                'code' => 'BRS-RL-001',
                'category_id' => 1,
                'price' => 75000,
                'cost_price' => 68000,
                'stock' => 50,
                'min_stock' => 10,
                'unit' => 'karung',
            ],
            [
                'name' => 'Minyak Goreng Bimoli 2L',
                'code' => 'MNY-BM-002',
                'category_id' => 1,
                'price' => 38000,
                'cost_price' => 35000,
                'stock' => 100,
                'min_stock' => 20,
                'unit' => 'pouch',
            ],
            [
                'name' => 'Gula Pasir Gulaku 1kg',
                'code' => 'GLA-GK-003',
                'category_id' => 1,
                'price' => 18000,
                'cost_price' => 16000,
                'stock' => 80,
                'min_stock' => 15,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Telur Ayam 1kg',
                'code' => 'TLR-AY-004',
                'category_id' => 1,
                'price' => 28000,
                'cost_price' => 25000,
                'stock' => 30,
                'min_stock' => 5,
                'unit' => 'kg',
            ],
            
            // Minuman (2)
            [
                'name' => 'Teh Pucuk Harum 350ml',
                'code' => 'MNM-TP-001',
                'category_id' => 2,
                'price' => 3500,
                'cost_price' => 2800,
                'stock' => 240,
                'min_stock' => 24,
                'unit' => 'botol',
            ],
            [
                'name' => 'Aqua 600ml',
                'code' => 'MNM-AQ-002',
                'category_id' => 2,
                'price' => 4000,
                'cost_price' => 3200,
                'stock' => 300,
                'min_stock' => 48,
                'unit' => 'botol',
            ],
            [
                'name' => 'Kopi Kapal Api Mix 1 Renceng',
                'code' => 'MNM-KA-003',
                'category_id' => 2,
                'price' => 15000,
                'cost_price' => 13500,
                'stock' => 50,
                'min_stock' => 5,
                'unit' => 'renceng',
            ],
            
            // Snack (3)
            [
                'name' => 'Indomie Goreng Original',
                'code' => 'SNK-ID-001',
                'category_id' => 3,
                'price' => 3100,
                'cost_price' => 2700,
                'stock' => 400,
                'min_stock' => 40,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Chitato Sapi Panggang 68g',
                'code' => 'SNK-CH-002',
                'category_id' => 3,
                'price' => 12000,
                'cost_price' => 10500,
                'stock' => 60,
                'min_stock' => 10,
                'unit' => 'pcs',
            ],
            
            // Bumbu Dapur (4)
            [
                'name' => 'Garam Cap Kapal 250g',
                'code' => 'BMB-GR-001',
                'category_id' => 4,
                'price' => 3000,
                'cost_price' => 2200,
                'stock' => 100,
                'min_stock' => 10,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Masako Ayam 100g',
                'code' => 'BMB-MS-002',
                'category_id' => 4,
                'price' => 5000,
                'cost_price' => 4500,
                'stock' => 150,
                'min_stock' => 15,
                'unit' => 'pcs',
            ],
            
            // Peralatan Mandi (5)
            [
                'name' => 'Sabun Lifebuoy Red 110g',
                'code' => 'MND-LB-001',
                'category_id' => 5,
                'price' => 4500,
                'cost_price' => 3800,
                'stock' => 72,
                'min_stock' => 12,
                'unit' => 'pcs',
            ],
            [
                'name' => 'Shampo Sunsilk Black 170ml',
                'code' => 'MND-SS-002',
                'category_id' => 5,
                'price' => 22000,
                'cost_price' => 19500,
                'stock' => 30,
                'min_stock' => 5,
                'unit' => 'botol',
            ],
        ];

        foreach ($products as $p) {
            Product::updateOrCreate(['code' => $p['code']], array_merge($p, [
                'is_active' => true,
                'description' => 'Produk sembako berkualitas untuk kebutuhan harian.',
            ]));
        }
    }
}
