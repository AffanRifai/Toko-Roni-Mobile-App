<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type');
                $table->string('license_plate')->unique();
                $table->decimal('capacity_weight', 10, 2)->nullable();
                $table->decimal('capacity_volume', 10, 2)->nullable();
                $table->string('status')->default('available');
                $table->date('last_maintenance')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
