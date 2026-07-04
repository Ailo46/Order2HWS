<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {

            $table->id();

            // -------------------------------------------------
            // Relations
            // -------------------------------------------------

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->foreignId('product_id')->nullable()->constrained();

            // -------------------------------------------------
            // Product Snapshot
            // -------------------------------------------------

            $table->string('product_code');

            $table->string('product_name');

            $table->string('brand_name');

            $table->string('category_name');

            // -------------------------------------------------
            // Packaging Snapshot
            // -------------------------------------------------

            $table->string('unit_name');

            $table->unsignedInteger('qty_per_pack');

            $table->decimal('size',10,2);

            $table->string('size_unit');

            // -------------------------------------------------
            // Order Values
            // -------------------------------------------------

            $table->integer('quantity');

            $table->boolean('sold_as_unit')->default(false);

            // -------------------------------------------------
            // Pricing Snapshot
            // -------------------------------------------------

            $table->decimal('unit_price',10,2);

            $table->decimal('discount_percent',5,2)->default(0);

            $table->decimal('vat_percent',5,2)->default(0);

            $table->decimal('line_total',10,2);

            // -------------------------------------------------
            // System
            // -------------------------------------------------

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};