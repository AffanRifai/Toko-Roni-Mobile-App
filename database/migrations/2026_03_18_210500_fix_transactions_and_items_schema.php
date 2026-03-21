<?php

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
        // Fix transactions table
        Schema::table('transactions', function (Blueprint $table) {
            // Rename total to total_amount if it exists and total_amount doesn't
            if (Schema::hasColumn('transactions', 'total') && !Schema::hasColumn('transactions', 'total_amount')) {
                $table->renameColumn('total', 'total_amount');
            } elseif (!Schema::hasColumn('transactions', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0)->after('user_id');
            }

            // Ensure total_amount is decimal
            if (Schema::hasColumn('transactions', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0)->change();
            }

            // Add missing columns
            if (!Schema::hasColumn('transactions', 'invoice_number')) {
                $table->string('invoice_number')->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('transactions', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('invoice_number');
            }
            if (!Schema::hasColumn('transactions', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('transactions', 'discount')) {
                $table->decimal('discount', 15, 2)->default(0)->after('customer_phone');
            }
            if (!Schema::hasColumn('transactions', 'cash_received')) {
                $table->decimal('cash_received', 15, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('transactions', 'change')) {
                $table->decimal('change', 15, 2)->default(0)->after('cash_received');
            }
            if (!Schema::hasColumn('transactions', 'payment_status')) {
                $table->string('payment_status')->default('LUNAS')->after('payment_method');
            }
            if (!Schema::hasColumn('transactions', 'due_date')) {
                $table->date('due_date')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('transactions', 'notes')) {
                $table->text('notes')->nullable()->after('due_date');
            }
        });

        // Fix transaction_items table
        Schema::table('transaction_items', function (Blueprint $table) {
            if (!Schema::hasColumn('transaction_items', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0)->after('price');
            }

            // Ensure price is decimal
            if (Schema::hasColumn('transaction_items', 'price')) {
                $table->decimal('price', 15, 2)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // We don't reverse renames here as it might be destructive if not careful
            $table->dropColumn([
                'invoice_number',
                'customer_name',
                'customer_phone',
                'discount',
                'cash_received',
                'change',
                'payment_status',
                'due_date',
                'notes'
            ]);
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('subtotal');
        });
    }
};
