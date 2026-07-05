<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->date('order_date')
                ->after('customer_id');

            $table->date('requested_delivery_date')
                ->nullable()
                ->after('order_date');

        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropColumn([
                'order_date',
                'requested_delivery_date',
            ]);

        });
    }
};