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
        Schema::table('customers', function (Blueprint $table) {

            if (! Schema::hasColumn('customers', 'default_discount_percent')) {

                $table
                    ->decimal('default_discount_percent', 5, 2)
                    ->default(0)
                    ->after('price_level_id');

            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            if (Schema::hasColumn('customers', 'default_discount_percent')) {

                $table->dropColumn('default_discount_percent');

            }

        });
    }
};