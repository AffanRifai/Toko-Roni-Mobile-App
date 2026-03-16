<?php
// database/migrations/[timestamp]_create_forecast_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->date('forecast_date');
            $table->decimal('predicted_quantity', 10, 2);
            $table->decimal('predicted_revenue', 15, 2);
            $table->decimal('lower_bound', 10, 2)->nullable();
            $table->decimal('upper_bound', 10, 2)->nullable();
            $table->decimal('confidence_level', 5, 2)->default(95.00);
            $table->string('period_type')->default('daily'); // daily, weekly, monthly
            $table->json('factors')->nullable(); // Faktor yang mempengaruhi
            $table->timestamps();

            $table->index(['product_id', 'forecast_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('forecasts');
    }
};
