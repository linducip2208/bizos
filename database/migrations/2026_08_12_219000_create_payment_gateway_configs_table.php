<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('gateway_type', 50); // midtrans, xendit, stripe, custom
            $table->text('api_key_encrypted');
            $table->text('api_secret_encrypted')->nullable();
            $table->text('server_key_encrypted')->nullable();
            $table->text('client_key_encrypted')->nullable();
            $table->string('base_url')->nullable();
            $table->json('extra_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'gateway_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_configs');
    }
};
