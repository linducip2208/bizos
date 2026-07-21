<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iso_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('incident_number')->unique();
            $table->string('title');
            $table->string('incident_type'); // phishing, malware, unauthorized_access, ddos, data_leak, insider, physical
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->text('description');
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->text('affected_assets')->nullable();
            $table->string('affected_systems')->nullable();
            $table->text('findings')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_actions')->nullable();
            $table->text('preventive_actions')->nullable();
            $table->string('status')->default('open'); // open, investigating, contained, resolved, closed
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('investigated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('reportable_to_regulator')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iso_incidents');
    }
};
