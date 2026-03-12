<?php
// database/migrations/2024_01_01_000002_create_receivables_table.php

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
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->string('no_piutang')->unique();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('transaction_id');
            $table->string('invoice_number');
            $table->date('tanggal_transaksi');
            $table->decimal('total_piutang', 15, 2);
            $table->decimal('sisa_piutang', 15, 2);
            $table->date('jatuh_tempo')->nullable();
            $table->enum('status', ['BELUM LUNAS', 'LUNAS'])->default('BELUM LUNAS');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            
            //index
            $table->index('no_piutang');
            $table->index('member_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivables');
    }
};
