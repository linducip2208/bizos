<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecommerce_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('channel_name');
            $table->text('api_key_encrypted')->nullable();
            $table->text('api_secret_encrypted')->nullable();
            $table->string('shop_id')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->boolean('is_active')->default(false);
            $table->dateTime('last_sync_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ecommerce_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('ecommerce_channels')->cascadeOnDelete();
            $table->string('channel_order_id');
            $table->dateTime('order_date');
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('shipping_method')->nullable();
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('channel_status')->default('unpaid');
            $table->string('sync_status')->default('pending');
            $table->foreignId('pos_transaction_id')->nullable()->constrained('pos_transactions')->nullOnDelete();
            $table->timestamps();

            $table->unique(['channel_id', 'channel_order_id']);
        });

        Schema::create('ecommerce_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_order_id')->constrained('ecommerce_orders')->cascadeOnDelete();
            $table->string('channel_sku');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });

        Schema::create('ecommerce_inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained('ecommerce_channels')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('old_stock', 12, 2)->default(0);
            $table->decimal('new_stock', 12, 2)->default(0);
            $table->decimal('channel_stock', 12, 2)->default(0);
            $table->string('sync_status')->default('pending');
            $table->dateTime('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_inventory_logs');
        Schema::dropIfExists('ecommerce_order_items');
        Schema::dropIfExists('ecommerce_orders');
        Schema::dropIfExists('ecommerce_channels');
    }
};
