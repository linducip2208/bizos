<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oauth_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('provider');
            $table->string('client_id');
            $table->text('client_secret_encrypted');
            $table->string('redirect_uri')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'provider']);
        });

        Schema::create('sso_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('provider');
            $table->string('metadata_url')->nullable();
            $table->string('entity_id')->nullable();
            $table->text('certificate')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'provider']);
        });

        Schema::create('shipping_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->text('api_key_encrypted')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('erp_connectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('target_erp');
            $table->json('connection_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'target_erp']);
        });

        Schema::create('erp_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('connector_id')->constrained('erp_connectors')->cascadeOnDelete();
            $table->string('entity_type');
            $table->enum('direction', ['import', 'export'])->default('import');
            $table->unsignedInteger('records_count')->default(0);
            $table->string('status')->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('connector_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_sync_logs');
        Schema::dropIfExists('erp_connectors');
        Schema::dropIfExists('shipping_providers');
        Schema::dropIfExists('sso_configs');
        Schema::dropIfExists('oauth_providers');
    }
};
