<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->json('method');
            $table->integer('gps_radius_meters')->nullable();
            $table->decimal('gps_latitude', 12, 8)->nullable();
            $table->decimal('gps_longitude', 12, 8)->nullable();
            $table->boolean('require_selfie')->default(true);
            $table->boolean('require_wfh_photo')->default(true);
            $table->boolean('auto_clock_out')->default(false);
            $table->time('auto_clock_out_time')->nullable();
            $table->json('weekend_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_configs');
    }
};
