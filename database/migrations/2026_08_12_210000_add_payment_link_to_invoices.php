<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_token', 100)->nullable()->unique()->after('status');
            $table->timestamp('payment_link_sent_at')->nullable()->after('payment_token');
            $table->timestamp('payment_link_expires_at')->nullable()->after('payment_link_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['payment_token']);
            $table->dropColumn(['payment_token', 'payment_link_sent_at', 'payment_link_expires_at']);
        });
    }
};
