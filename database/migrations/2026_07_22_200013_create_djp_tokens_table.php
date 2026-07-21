<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('djp_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('npwp');
            $table->text('access_token_encrypted')->nullable();
            $table->text('refresh_token_encrypted')->nullable();
            $table->string('certificate_path')->nullable();
            $table->string('certificate_password_encrypted')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->string('status')->default('active'); // active, expired, revoked, error
            $table->json('last_api_response')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('djp_tokens');
    }
};
