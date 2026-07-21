<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('plate_number')->unique();
            $table->string('brand');
            $table->string('model')->nullable();
            $table->year('year')->nullable();
            $table->string('vehicle_type')->default('car')->comment('car, motorcycle, truck');
            $table->string('fuel_type')->default('gasoline')->comment('gasoline, diesel, electric, hybrid');
            $table->string('ownership')->default('company')->comment('company, leased');
            $table->string('status')->default('available')->comment('available, in_use, maintenance, sold');
            $table->bigInteger('last_odometer')->default(0);
            $table->string('color')->nullable();
            $table->string('chassis_number')->nullable();
            $table->string('engine_number')->nullable();
            $table->date('registration_expiry')->nullable();
            $table->date('insurance_expiry')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
