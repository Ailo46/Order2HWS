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
        Schema::create('units', function (Blueprint $table) {

            $table->id();

            // Identity
            $table->string('code')->unique();
            $table->string('name');

            // Optional
            $table->text('description')->nullable();

            // Display
            $table->unsignedSmallInteger('sort_order')->default(0);

            // Status
            $table->boolean('is_active')->default(true);

            // System
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};