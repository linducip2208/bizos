<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->bigInteger('odometer');
            $table->decimal('liters', 10, 2);
            $table->decimal('cost', 15, 2);
            $table->string('fuel_type')->default('gasoline');
            $table->string('station')->nullable();
            $table->string('receipt_photo')->nullable();
            $table->decimal('fuel_efficiency', 8, 2)->nullable()->comment('km/L auto-calculated');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_fuel_logs');
    }
};
