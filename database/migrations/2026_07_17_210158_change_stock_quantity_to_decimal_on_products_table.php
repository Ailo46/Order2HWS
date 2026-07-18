<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
         * Laravel's change() requires doctrine/dbal.
         * To keep the project lightweight we use raw SQL.
         */

        DB::statement("
            ALTER TABLE products
            MODIFY stock_quantity DECIMAL(12,3) NOT NULL DEFAULT 0
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE products
            MODIFY stock_quantity INT NOT NULL DEFAULT 0
        ");
    }
};