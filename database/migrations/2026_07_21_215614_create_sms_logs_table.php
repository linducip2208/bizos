<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('gateway_id')->nullable()->constrained('sms_gateways')->nullOnDelete();
            $table->string('recipient', 20);
            $table->text('message');
            $table->string('status')->default('queued')->comment('queued, sent, delivered, failed');
            $table->string('message_id')->nullable()->comment('provider message ID');
            $table->decimal('cost', 10, 2)->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('recipient');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
