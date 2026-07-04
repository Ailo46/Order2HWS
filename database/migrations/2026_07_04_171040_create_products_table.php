<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {

            $table->id();

            // Identity
            $table->string('code',20)->unique();
            $table->string('barcode')->nullable();
            $table->string('sku')->nullable();

            // Classification
            $table->foreignId('brand_id')->constrained()->cascadeOnUpdate();
            $table->foreignId('category_id')->constrained()->cascadeOnUpdate();

            // Details
            $table->string('name');
            $table->text('short_description')->nullable();

            // Packaging
            $table->foreignId('unit_id')->constrained()->cascadeOnUpdate();

            $table->integer('qty_per_pack')->default(1);

            $table->decimal('size',10,2);

            $table->foreignId('size_unit_id')->constrained()->cascadeOnUpdate();

            $table->boolean('can_be_sold_as_unit')->default(false);

            // Pricing
            $table->decimal('base_price',10,2);

            $table->decimal('special_offer_percent',5,2)->default(0);

            $table->decimal('vat_percent',5,2)->default(0);

            // Inventory
            $table->integer('stock_quantity')->default(0);

            // Media
            $table->string('image')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->softDeletes();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};