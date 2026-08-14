<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('gateway_type', 50)->nullable()->after('code');
            $table->foreignId('gateway_config_id')->nullable()->after('gateway_type')
                ->constrained('payment_gateway_configs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gateway_config_id');
            $table->dropColumn('gateway_type');
        });
    }
};
