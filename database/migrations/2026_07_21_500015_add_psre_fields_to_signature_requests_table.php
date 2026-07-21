<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signature_requests', function (Blueprint $table) {
            $table->string('psre_provider_name')->nullable()->after('provider');
            $table->boolean('psre_registered')->default(false)->after('psre_provider_name');
            $table->string('psre_certificate_number')->nullable()->after('psre_registered');
            $table->timestamp('psre_certificate_valid_until')->nullable()->after('psre_certificate_number');
            $table->json('audit_trail')->nullable()->after('completed_at');
            $table->boolean('two_factor_required')->default(false)->after('audit_trail');
            $table->timestamp('two_factor_verified_at')->nullable()->after('two_factor_required');
            $table->boolean('legal_certificate_generated')->default(false)->after('two_factor_verified_at');
            $table->string('legal_certificate_path')->nullable()->after('legal_certificate_generated');
            $table->string('geo_location')->nullable()->after('legal_certificate_path');
        });
    }

    public function down(): void
    {
        Schema::table('signature_requests', function (Blueprint $table) {
            $table->dropColumn([
                'psre_provider_name', 'psre_registered', 'psre_certificate_number',
                'psre_certificate_valid_until', 'audit_trail', 'two_factor_required',
                'two_factor_verified_at', 'legal_certificate_generated', 'legal_certificate_path', 'geo_location',
            ]);
        });
    }
};
