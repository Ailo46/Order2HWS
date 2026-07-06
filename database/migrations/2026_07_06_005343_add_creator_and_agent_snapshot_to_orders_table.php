<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->foreignId('created_by')
                ->nullable()
                ->after('customer_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('agent_name')
                ->nullable()
                ->after('created_by');

            $table->string('agent_code', 2)
                ->nullable()
                ->after('agent_name');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropForeign(['created_by']);

            $table->dropColumn([
                'created_by',
                'agent_name',
                'agent_code',
            ]);
        });
    }
};