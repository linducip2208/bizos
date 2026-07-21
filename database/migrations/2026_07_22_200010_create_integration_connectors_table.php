<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_connectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('connector_type'); // jurnal_id, xero, quickbooks, google_workspace, microsoft_365, open_banking, djp
            $table->string('name');
            $table->string('status')->default('disconnected'); // disconnected, connecting, connected, error
            $table->json('credentials_encrypted')->nullable();
            $table->json('configuration')->nullable(); // sync settings, mapped entities
            $table->json('last_sync_result')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_connectors');
    }
};
