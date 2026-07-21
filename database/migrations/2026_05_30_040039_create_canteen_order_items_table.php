<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canteen_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('canteen_orders')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('canteen_menus')->restrictOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canteen_order_items');
    }
};
