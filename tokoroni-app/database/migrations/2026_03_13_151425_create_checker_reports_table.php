<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_checker_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('checker_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('report_type', ['low_stock', 'expiring', 'expired', 'damaged', 'other']);
            $table->text('notes');
            $table->integer('quantity')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'rejected'])->default('pending');
            $table->timestamp('reported_at');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('checker_reports');
    }
};