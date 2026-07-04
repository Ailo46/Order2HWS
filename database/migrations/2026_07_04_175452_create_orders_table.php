<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {

            $table->id();

            // -------------------------------------------------
            // Identity
            // -------------------------------------------------

            $table->string('order_number')->unique();

            // -------------------------------------------------
            // Relations
            // -------------------------------------------------

            $table->foreignId('customer_id')->constrained();

            // عامل فروش ثبت کننده
            $table->foreignId('sales_agent_id')
                ->nullable()
                ->constrained('users');

            // -------------------------------------------------
            // Workflow
            // -------------------------------------------------

            $table->string('status')->default('draft');

            // -------------------------------------------------
            // Totals
            // -------------------------------------------------

            $table->decimal('subtotal',10,2)->default(0);

            $table->decimal('discount_total',10,2)->default(0);

            $table->decimal('vat_total',10,2)->default(0);

            $table->decimal('grand_total',10,2)->default(0);

            // -------------------------------------------------
            // Notes
            // -------------------------------------------------

            $table->text('customer_note')->nullable();

            $table->text('internal_note')->nullable();

            // -------------------------------------------------
            // Dates
            // -------------------------------------------------

            $table->timestamp('submitted_at')->nullable();

            $table->timestamp('confirmed_at')->nullable();

            $table->timestamp('delivered_at')->nullable();

            // -------------------------------------------------
            // System
            // -------------------------------------------------

            $table->timestamps();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};