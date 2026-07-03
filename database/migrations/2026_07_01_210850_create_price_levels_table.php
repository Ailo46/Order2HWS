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
        Schema::create('price_levels', function (Blueprint $table) {
            $table->id();

            // -----------------------------------------------------------------
            // Identity
            // -----------------------------------------------------------------

            $table->string('code')->unique();
            $table->string('name')->unique();

            // -----------------------------------------------------------------
            // Pricing
            // -----------------------------------------------------------------

            $table->decimal('price_adjustment_percent', 8, 2)->default(0);

            // -----------------------------------------------------------------
            // Description
            // -----------------------------------------------------------------

            $table->text('description')->nullable();

            // -----------------------------------------------------------------
            // System
            // -----------------------------------------------------------------

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_levels');
    }
};
