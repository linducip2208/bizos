<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->date('record_date');
            $table->string('source'); // municipal, well, rainwater, recycled, surface_water
            $table->decimal('quantity_m3', 12, 3);
            $table->string('purpose'); // production, sanitation, cooling, irrigation, domestic
            $table->decimal('cost', 15, 2)->nullable();
            $table->string('meter_number')->nullable();
            $table->decimal('meter_reading_start', 12, 3)->nullable();
            $table->decimal('meter_reading_end', 12, 3)->nullable();
            $table->boolean('is_recycled')->default(false);
            $table->decimal('recycled_percentage', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_usages');
    }
};
