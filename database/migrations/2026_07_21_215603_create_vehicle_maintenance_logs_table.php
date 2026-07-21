<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('type')->default('routine')->comment('routine, repair, inspection');
            $table->string('description');
            $table->decimal('cost', 15, 2)->default(0);
            $table->string('vendor')->nullable();
            $table->bigInteger('odometer_at')->default(0);
            $table->bigInteger('next_odometer_due')->nullable();
            $table->date('date');
            $table->date('next_due_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_logs');
    }
};
