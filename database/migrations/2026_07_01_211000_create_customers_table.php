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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // -----------------------------------------------------------------
            // Identity
            // -----------------------------------------------------------------
            
            $table->string('code')->nullable()->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            // -----------------------------------------------------------------
            // Business Classification
            // -----------------------------------------------------------------
            
            $table->foreignId('customer_type_id')->constrained();
            $table->foreignId('price_level_id')->constrained();
            
            // -----------------------------------------------------------------
            // Pricing
            // -----------------------------------------------------------------
            
            $table->decimal('default_discount_percent', 5, 2)->default(0);
            
            // -----------------------------------------------------------------
            // Assignment
            // -----------------------------------------------------------------
            
            $table->foreignId('sales_agent_id')->nullable();
            
            // -----------------------------------------------------------------
            // Contact Information
            // -----------------------------------------------------------------
            
            $table->string('contact_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('email')->nullable()->index();
            
            // -----------------------------------------------------------------
            // Address
            // -----------------------------------------------------------------
            
            $table->text('address')->nullable();
            
            // -----------------------------------------------------------------
            // System
            // -----------------------------------------------------------------

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
