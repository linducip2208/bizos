<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('room_number', 20);
            $table->string('room_type', 30)->default('standard');
            $table->integer('floor')->default(1);
            $table->string('bed_type', 20)->default('double');
            $table->integer('max_guests')->default(2);
            $table->decimal('base_price', 15, 2)->default(0);
            $table->decimal('weekend_price', 15, 2)->nullable();
            $table->decimal('holiday_price', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->string('status', 20)->default('available');
            $table->string('current_guest_name')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'room_number'], 'rooms_company_number_unique');
        });

        Schema::create('room_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->string('guest_name', 200);
            $table->string('guest_phone', 30)->nullable();
            $table->string('guest_email', 200)->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            $table->string('booking_source', 30)->default('direct');
            $table->string('ota_booking_id', 100)->nullable();
            $table->decimal('ota_commission_percent', 5, 2)->nullable();
            $table->decimal('total_room_charge', 15, 2)->default(0);
            $table->string('status', 20)->default('pending');
            $table->text('special_requests')->nullable();
            $table->timestamps();
        });

        Schema::create('hotel_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 200);
            $table->string('category', 30)->default('other');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('guest_folios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('room_bookings')->restrictOnDelete();
            $table->string('folio_number', 50);
            $table->decimal('total_room_charges', 15, 2)->default(0);
            $table->decimal('total_service_charges', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('deposit_paid', 15, 2)->default(0);
            $table->decimal('balance_due', 15, 2)->default(0);
            $table->string('payment_status', 20)->default('pending');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('folio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folio_id')->constrained('guest_folios')->restrictOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('hotel_services')->nullOnDelete();
            $table->foreignId('pos_transaction_id')->nullable()->constrained('pos_transactions')->nullOnDelete();
            $table->string('description', 200);
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('charge_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folio_items');
        Schema::dropIfExists('guest_folios');
        Schema::dropIfExists('hotel_services');
        Schema::dropIfExists('room_bookings');
        Schema::dropIfExists('rooms');
    }
};
