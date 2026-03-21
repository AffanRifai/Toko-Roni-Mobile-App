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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'face_descriptor')) {
                $table->longText('face_descriptor')->nullable()->after('image');
            }
            if (!Schema::hasColumn('users', 'face_score')) {
                $table->float('face_score')->nullable()->after('face_descriptor');
            }
            if (!Schema::hasColumn('users', 'face_registered_at')) {
                $table->timestamp('face_registered_at')->nullable()->after('face_score');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('face_registered_at');
            }
            if (!Schema::hasColumn('users', 'jenis_toko')) {
                $table->string('jenis_toko')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('users', 'current_lat')) {
                $table->decimal('current_lat', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('users', 'current_lng')) {
                $table->decimal('current_lng', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('users', 'last_location_update')) {
                $table->timestamp('last_location_update')->nullable();
            }
            if (!Schema::hasColumn('users', 'delivery_status')) {
                $table->string('delivery_status')->default('inactive');
            }
            if (!Schema::hasColumn('users', 'delivery_rating')) {
                $table->float('delivery_rating')->default(0);
            }
            if (!Schema::hasColumn('users', 'delivery_rating_count')) {
                $table->integer('delivery_rating_count')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'face_descriptor',
                'face_score',
                'face_registered_at',
                'is_active',
                'jenis_toko',
                'current_lat',
                'current_lng',
                'last_location_update',
                'delivery_status',
                'delivery_rating',
                'delivery_rating_count'
            ]);
        });
    }
};
