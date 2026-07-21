<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('do_number')->unique();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('pos_transaction_id')->nullable()->constrained('pos_transactions')->nullOnDelete();
            $table->string('customer_name');
            $table->text('delivery_address');
            $table->date('delivery_date');
            $table->foreignId('driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->dateTime('estimated_arrival')->nullable();
            $table->dateTime('actual_arrival')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_signature_path')->nullable();
            $table->string('pod_photo_path')->nullable();
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->string('unit')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->cascadeOnDelete();
            $table->integer('stop_sequence')->default(1);
            $table->text('address');
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->dateTime('planned_arrival')->nullable();
            $table->dateTime('actual_arrival')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('cold_chain_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained('delivery_orders')->cascadeOnDelete();
            $table->string('sensor_id')->nullable();
            $table->decimal('temperature_celsius', 5, 2)->nullable();
            $table->decimal('humidity_percent', 5, 2)->nullable();
            $table->dateTime('recorded_at');
            $table->boolean('is_breached')->default(false);
            $table->string('breach_details')->nullable();
            $table->timestamps();
        });

        Schema::create('fleet_gps_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('speed_kmh', 6, 1)->nullable();
            $table->decimal('heading', 5, 1)->nullable();
            $table->boolean('ignition_status')->default(true);
            $table->dateTime('recorded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fleet_gps_tracks');
        Schema::dropIfExists('cold_chain_logs');
        Schema::dropIfExists('delivery_stops');
        Schema::dropIfExists('delivery_items');
        Schema::dropIfExists('delivery_orders');
    }
};
