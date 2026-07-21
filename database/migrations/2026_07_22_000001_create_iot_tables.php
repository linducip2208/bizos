<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iot_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name', 255);
            $table->string('device_token', 128)->unique();
            $table->enum('type', ['sensor_temperature', 'sensor_vibration', 'energy_meter', 'rfid_reader', 'smart_scale']);
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('location', 255)->nullable();
            $table->enum('status', ['online', 'offline', 'maintenance', 'error'])->default('offline');
            $table->json('config')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->decimal('battery_level', 5, 2)->nullable();
            $table->string('firmware_version', 50)->nullable();
            $table->date('installed_at')->nullable();
            $table->date('next_maintenance_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('iot_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('iot_device_id')->constrained('iot_devices')->cascadeOnDelete();
            $table->decimal('temperature_celsius', 6, 2)->nullable();
            $table->decimal('humidity_percent', 5, 2)->nullable();
            $table->decimal('vibration_mm_s', 8, 4)->nullable();
            $table->decimal('pressure_hpa', 7, 2)->nullable();
            $table->decimal('battery_level', 5, 2)->nullable();
            $table->decimal('signal_strength_dbm', 5, 2)->nullable();
            $table->json('raw_payload')->nullable();
            $table->json('extra_data')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['iot_device_id', 'recorded_at']);
            $table->index('recorded_at');
        });

        Schema::create('iot_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('iot_device_id')->constrained('iot_devices')->cascadeOnDelete();
            $table->enum('type', ['threshold_breach', 'rate_of_change', 'anomaly', 'battery_low', 'offline', 'predictive_maintenance']);
            $table->enum('severity', ['info', 'warning', 'critical'])->default('warning');
            $table->string('title', 255);
            $table->text('message');
            $table->json('details')->nullable();
            $table->enum('status', ['active', 'acknowledged', 'resolved'])->default('active');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('energy_meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('iot_device_id')->nullable()->constrained('iot_devices')->nullOnDelete();
            $table->string('name', 255);
            $table->string('meter_number', 100)->unique();
            $table->string('location', 255)->nullable();
            $table->decimal('rate_per_kwh', 10, 2)->default(0);
            $table->string('utility_provider', 100)->nullable();
            $table->decimal('total_kwh_lifetime', 15, 3)->default(0);
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('energy_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('energy_meter_id')->constrained('energy_meters')->cascadeOnDelete();
            $table->decimal('kwh', 12, 3);
            $table->decimal('voltage', 6, 1)->nullable();
            $table->decimal('current_amps', 6, 1)->nullable();
            $table->decimal('power_factor', 4, 2)->nullable();
            $table->decimal('frequency_hz', 5, 2)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['energy_meter_id', 'recorded_at']);
            $table->index('recorded_at');
        });

        Schema::create('rfid_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('rfid_code', 128)->unique();
            $table->string('epc', 255)->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('batch_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'in_stock', 'dispatched', 'returned', 'decommissioned'])->default('active');
            $table->timestamp('last_scanned_at')->nullable();
            $table->foreignId('last_scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('last_known_location', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('smart_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('iot_device_id')->nullable()->constrained('iot_devices')->nullOnDelete();
            $table->string('name', 255);
            $table->string('scale_number', 100)->unique();
            $table->string('location', 255)->nullable();
            $table->decimal('max_capacity_kg', 10, 2);
            $table->decimal('precision_g', 8, 2)->default(1);
            $table->decimal('tare_weight_kg', 8, 3)->default(0);
            $table->foreignId('linked_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->decimal('low_stock_threshold_kg', 8, 3)->nullable();
            $table->decimal('current_weight_kg', 10, 3)->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_reading_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('scale_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('smart_scale_id')->constrained('smart_scales')->cascadeOnDelete();
            $table->decimal('weight_kg', 10, 3);
            $table->decimal('weight_net_kg', 10, 3)->nullable();
            $table->boolean('is_stable')->default(true);
            $table->boolean('is_low_stock')->default(false);
            $table->json('raw_payload')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['smart_scale_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scale_readings');
        Schema::dropIfExists('smart_scales');
        Schema::dropIfExists('rfid_tags');
        Schema::dropIfExists('energy_readings');
        Schema::dropIfExists('energy_meters');
        Schema::dropIfExists('iot_alerts');
        Schema::dropIfExists('iot_readings');
        Schema::dropIfExists('iot_devices');
    }
};
