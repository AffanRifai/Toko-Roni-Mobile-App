<?php
// database/migrations/2024_01_01_000004_add_member_columns_to_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Ubah enum payment_method jika perlu
            $table->string('payment_method')->default('tunai')->change();

            // Tambah kolom baru
            $table->unsignedBigInteger('member_id')->nullable()->after('user_id');
            $table->enum('payment_status', ['LUNAS', 'BELUM LUNAS'])->default('LUNAS')->after('payment_method');
            $table->date('due_date')->nullable()->after('payment_status');
            $table->text('notes')->nullable()->after('due_date');

            // Foreign key
            $table->foreign('member_id')->references('id')->on('members')->onDelete('set null');

            // Indexes
            $table->index('member_id');
            $table->index('payment_status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->dropColumn(['member_id', 'payment_status', 'due_date', 'notes']);
        });
    }
};
