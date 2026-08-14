<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->unsignedBigInteger('warehouse_id')->nullable()->change();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
            $table->softDeletes();
        });

        Schema::table('stock_balances', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->unsignedBigInteger('warehouse_id')->nullable()->change();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->nullOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('stock_balances', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
