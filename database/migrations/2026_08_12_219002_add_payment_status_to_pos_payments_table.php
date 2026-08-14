<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            // Diperlukan supaya webhook gateway bisa mencatat status per baris pembayaran.
            // Baris lama dianggap sudah lunas (paid) karena dibuat sebelum fitur ini.
            $table->string('payment_status', 20)->default('paid')->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }
};
