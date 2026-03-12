<?php
// database/migrations/2024_02_18_000001_add_delivery_fields_to_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('need_delivery')->default(false)->after('notes');
            $table->text('delivery_address')->nullable()->after('need_delivery');
            $table->string('recipient_name')->nullable()->after('delivery_address');
            $table->string('recipient_phone')->nullable()->after('recipient_name');
            $table->json('items_to_deliver')->nullable()->after('recipient_phone');
            $table->json('items_taken')->nullable()->after('items_to_deliver');
            $table->date('desired_delivery_date')->nullable()->after('items_taken');
            $table->text('delivery_notes')->nullable()->after('desired_delivery_date');
            $table->decimal('delivery_fee', 15, 2)->default(0)->after('delivery_notes');
            $table->string('delivery_status')->nullable()->after('delivery_fee');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'need_delivery',
                'delivery_address',
                'recipient_name',
                'recipient_phone',
                'items_to_deliver',
                'items_taken',
                'desired_delivery_date',
                'delivery_notes',
                'delivery_fee',
                'delivery_status'
            ]);
        });
    }
};
