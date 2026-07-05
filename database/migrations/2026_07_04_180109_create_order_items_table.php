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

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnUpdate();

            $table->json('product_snapshot');

            $table->decimal('quantity',10,2);

            $table->decimal('unit_price',10,2);

            $table->decimal('discount_percent',5,2)->default(0);

            $table->decimal('discount_amount',10,2)->default(0);

            $table->decimal('vat_percent',5,2)->default(0);

            $table->decimal('vat_amount',10,2)->default(0);

            $table->decimal('line_total',10,2);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};