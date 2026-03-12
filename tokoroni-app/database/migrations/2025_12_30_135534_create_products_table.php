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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('profit_margin', 5, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(10);
            $table->string('unit')->default('pcs');
            $table->foreignId('supplier_id')->nullable()->nullOnDelete();
            $table->string('barcode')->nullable()->unique();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('dimensions')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes(); // ✅ WAJIB
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
