<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('system_prompt');
            $table->string('model')->default('gpt-4o-mini');
            $table->foreignId('provider_id')->constrained('ai_providers')->restrictOnDelete();
            $table->json('tools')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });

        Schema::create('ai_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_event');
            $table->foreignId('agent_id')->constrained('ai_agents')->restrictOnDelete();
            $table->json('steps')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'trigger_event']);
            $table->index(['company_id', 'is_active']);
        });

        Schema::create('ai_prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->default('general');
            $table->text('content');
            $table->json('variables')->nullable();
            $table->boolean('is_template')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'category']);
        });

        Schema::create('speech_transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('audio_path');
            $table->text('transcript')->nullable();
            $table->string('language', 10)->default('id-ID');
            $table->decimal('duration', 10, 2)->nullable();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('company_id');
            $table->index('user_id');
        });

        Schema::create('vision_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('image_path');
            $table->text('prompt')->nullable();
            $table->text('result')->nullable();
            $table->string('model_used')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vision_analyses');
        Schema::dropIfExists('speech_transcripts');
        Schema::dropIfExists('ai_prompts');
        Schema::dropIfExists('ai_workflows');
        Schema::dropIfExists('ai_agents');
    }
};
