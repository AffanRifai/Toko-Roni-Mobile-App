<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('deliveries')) {
            Schema::create('deliveries', function (Blueprint $table) {
                $table->id();
                $table->string('delivery_code')->unique();
                $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
                $table->string('origin')->nullable();
                $table->string('destination')->nullable();
                $table->integer('total_items')->default(0);
                $table->decimal('total_weight', 10, 2)->default(0);
                $table->decimal('total_volume', 10, 2)->default(0);
                $table->string('status')->default('pending');
                $table->dateTime('estimated_delivery_time')->nullable();
                $table->dateTime('delivered_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
