<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LargeDataSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate to avoid duplicates
        Schema::disableForeignKeyConstraints();
        DB::table('transaction_items')->truncate();
        DB::table('transactions')->truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Create Categories if not exist
        $categories = ['Sembako', 'Minuman', 'Snack', 'Bumbu Dapur', 'Peralatan Mandi'];
        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['name' => $cat],
                ['slug' => \Illuminate\Support\Str::slug($cat), 'is_active' => true]
            );
        }
        $catIds = Category::pluck('id')->toArray();

        // 2. Create Kasir if not exist
        if (User::where('role', 'kasir')->count() == 0) {
            User::create([
                'name' => 'Kasir Dummy',
                'email' => 'kasir@dummy.com',
                'password' => bcrypt('password'),
                'role' => 'kasir',
                'is_active' => true
            ]);
        }
        $kasirId = User::where('role', 'kasir')->first()->id;

        // 3. Create 50 Products
        Product::factory(50)->create([
            'category_id' => function() use ($catIds) {
                return $catIds[array_rand($catIds)];
            }
        ]);
        $products = Product::all();

        // 4. Create Transactions for 6 months
        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now();

        $this->command->info('Seeding transactions...');

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            // Generate 5-15 transactions per day
            $dailyTrxCount = rand(5, 15);
            
            for ($i = 0; $i < $dailyTrxCount; $i++) {
                $transaction = Transaction::create([
                    'invoice_number' => 'TRX-' . $date->format('Ymd') . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'customer_name' => 'Pelanggan ' . rand(1, 100),
                    'user_id' => $kasirId,
                    'total_amount' => 0,
                    'payment_method' => rand(0, 10) > 2 ? 'tunai' : 'transfer',
                    'payment_status' => 'LUNAS',
                    'created_at' => $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59)),
                ]);

                // 1-5 items per transaction
                $itemCount = rand(1, 5);
                $totalAmount = 0;

                for ($j = 0; $j < $itemCount; $j++) {
                    $product = $products->random();
                    $qty = rand(1, 5);
                    $subtotal = $qty * $product->price;

                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $product->id,
                        'qty' => $qty,
                        'price' => $product->price,
                        'subtotal' => $subtotal,
                        'created_at' => $transaction->created_at,
                    ]);

                    $totalAmount += $subtotal;
                }

                $transaction->update([
                    'total_amount' => $totalAmount,
                    'cash_received' => ceil($totalAmount / 1000) * 1000,
                    'change' => (ceil($totalAmount / 1000) * 1000) - $totalAmount
                ]);
            }
        }

        $this->command->info('Seeding completed!');
    }
}
