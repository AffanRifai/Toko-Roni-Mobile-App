<?php
// database/migrations/2024_01_01_000003_create_receivable_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receivable_id');
            $table->date('tanggal_bayar');
            $table->decimal('jumlah_bayar', 15, 2);
            $table->enum('metode_bayar', ['tunai', 'transfer'])->default('tunai');
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('kasir_id');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('receivable_id')->references('id')->on('receivables')->onDelete('cascade');
            $table->foreign('kasir_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivable_payments');
    }
};
