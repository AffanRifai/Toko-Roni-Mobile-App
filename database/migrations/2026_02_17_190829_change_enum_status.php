<?php
// database/migrations/2024_02_18_000006_change_status_to_varchar_in_vehicles.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah kolom status dari ENUM ke VARCHAR
        DB::statement("ALTER TABLE vehicles MODIFY status VARCHAR(20) DEFAULT 'available'");
    }

    public function down(): void
    {
        // Kembalikan ke ENUM jika perlu
        DB::statement("ALTER TABLE vehicles MODIFY status ENUM('available', 'in_use', 'maintenance') DEFAULT 'available'");
    }
};
