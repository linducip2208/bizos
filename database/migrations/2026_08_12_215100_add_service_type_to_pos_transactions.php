<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->foreignId('service_type_id')->nullable()->after('shift_id')->constrained('service_types')->nullOnDelete();
            $table->foreignId('service_staff_id')->nullable()->after('cashier_id')->constrained('employees')->nullOnDelete();
            $table->decimal('delivery_fee', 20, 2)->nullable()->after('grand_total');
            $table->text('delivery_address')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('pos_transactions', function (Blueprint $table) {
            $table->dropForeign(['service_type_id']);
            $table->dropForeign(['service_staff_id']);
            $table->dropColumn(['service_type_id', 'service_staff_id', 'delivery_fee', 'delivery_address']);
        });
    }
};
