<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('deliveries') || !Schema::hasColumn('deliveries', 'user_id')) {
            return;
        }

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE deliveries MODIFY user_id BIGINT UNSIGNED NULL');

        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('deliveries') || !Schema::hasColumn('deliveries', 'user_id')) {
            return;
        }

        $fallbackUserId = DB::table('users')->min('id');
        if ($fallbackUserId === null) {
            return;
        }

        DB::table('deliveries')
            ->whereNull('user_id')
            ->update(['user_id' => $fallbackUserId]);

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        DB::statement('ALTER TABLE deliveries MODIFY user_id BIGINT UNSIGNED NOT NULL');

        Schema::table('deliveries', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};

