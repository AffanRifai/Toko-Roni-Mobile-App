<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $costPrice = $this->faker->randomFloat(2, 1000, 50000);
        $price = $costPrice * 1.3;

        return [
            'name' => $name,
            'code' => 'PROD-' . strtoupper(Str::random(8)),
            'category_id' => Category::factory(),
            'description' => $this->faker->paragraph,
            'cost_price' => $costPrice,
            'price' => $price,
            'profit_margin' => 30.00,
            'stock' => $this->faker->numberBetween(0, 100),
            'min_stock' => 10,
            'unit' => 'pcs',
            'is_active' => true,
        ];
    }
}
