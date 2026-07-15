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
        Schema::table('products', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Special Offer
            |--------------------------------------------------------------------------
            */

            $table->boolean('offer_active')
                ->default(false)
                ->after('special_offer_percent');

            $table->timestamp('offer_start_at')
                ->nullable()
                ->after('offer_active');

            $table->timestamp('offer_end_at')
                ->nullable()
                ->after('offer_start_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {

            $table->dropColumn([
                'offer_active',
                'offer_start_at',
                'offer_end_at',
            ]);

        });
    }
};