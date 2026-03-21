<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'invoice_number' => 'TRX-' . strtoupper(Str::random(10)),
            'customer_name' => $this->faker->name,
            'customer_phone' => $this->faker->phoneNumber,
            'user_id' => User::where('role', 'kasir')->inRandomOrder()->first()?->id ?? User::factory(),
            'total_amount' => 0, // Calculated after items
            'cash_received' => 0,
            'change' => 0,
            'payment_method' => $this->faker->randomElement(['tunai', 'transfer', 'kredit']),
            'payment_status' => 'LUNAS',
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
