<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('specialization')->nullable()->after('overtime_rate');
            $table->string('sip_number', 50)->nullable()->after('specialization');
            $table->string('str_number', 50)->nullable()->after('sip_number');
            $table->decimal('consultation_fee', 15, 2)->nullable()->after('str_number');
            $table->boolean('is_doctor')->default(false)->after('consultation_fee');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['specialization', 'sip_number', 'str_number', 'consultation_fee', 'is_doctor']);
        });
    }
};
