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
        Schema::create('products', function (Blueprint $table) {

            $table->id();

            // ---------------------------------------------------------
            // Identity
            // ---------------------------------------------------------

            $table->string('code')->unique();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();

            // ---------------------------------------------------------
            // Classification
            // ---------------------------------------------------------

            $table->foreignId('category_id')->constrained();
            $table->foreignId('brand_id')->constrained();

            // ---------------------------------------------------------
            // Product
            // ---------------------------------------------------------

            $table->string('name');
            $table->text('short_description')->nullable();

            // ---------------------------------------------------------
            // Packaging
            // ---------------------------------------------------------

            $table->foreignId('unit_id')->constrained();

            $table->unsignedInteger('qty_per_pack')->default(1);

            $table->decimal('size',10,2);

            $table->foreignId('size_unit_id')->constrained();

            // ---------------------------------------------------------
            // Selling
            // ---------------------------------------------------------

            $table->boolean('can_sell_unit')->default(false);

            // ---------------------------------------------------------
            // Pricing
            // ---------------------------------------------------------

            $table->decimal('base_price',10,2);

            $table->decimal('vat_percent',5,2)->default(0);

            $table->decimal('special_offer_percent',5,2)->default(0);

            // ---------------------------------------------------------
            // Inventory
            // ---------------------------------------------------------

            $table->integer('stock_quantity')->default(0);

            $table->integer('minimum_stock')->default(0);

            // ---------------------------------------------------------
            // Visibility
            // ---------------------------------------------------------

            $table->boolean('visible_consumer')->default(true);

            $table->boolean('visible_cash_customer')->default(true);

            $table->boolean('visible_sales_agent')->default(true);

            // ---------------------------------------------------------
            // Product Status
            // ---------------------------------------------------------

            $table->date('expiry_date')->nullable();

            $table->boolean('is_active')->default(true);

            // ---------------------------------------------------------
            // Media
            // ---------------------------------------------------------

            $table->string('image')->nullable();

            // ---------------------------------------------------------
            // System
            // ---------------------------------------------------------

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};