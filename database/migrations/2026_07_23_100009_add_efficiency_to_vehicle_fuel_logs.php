<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_fuel_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_fuel_logs', 'fuel_efficiency_kmpl')) {
                $table->decimal('fuel_efficiency_kmpl', 8, 2)->nullable()->after('fuel_efficiency');
            }
            if (!Schema::hasColumn('vehicle_fuel_logs', 'cost_per_km')) {
                $table->decimal('cost_per_km', 10, 2)->nullable()->after('fuel_efficiency_kmpl');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_fuel_logs', function (Blueprint $table) {
            $table->dropColumn(['cost_per_km', 'fuel_efficiency_kmpl']);
        });
    }
};
