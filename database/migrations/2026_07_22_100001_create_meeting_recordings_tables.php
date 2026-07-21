<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->string('provider', 50);
            $table->string('provider_recording_id', 255)->nullable();
            $table->string('file_name', 500);
            $table->string('file_path', 1000);
            $table->bigInteger('file_size_bytes')->default(0);
            $table->integer('duration_seconds')->default(0);
            $table->string('file_type', 50)->default('mp4');
            $table->enum('status', ['processing', 'ready', 'failed'])->default('processing');
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('meeting_transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('recording_id')->nullable()->constrained('meeting_recordings')->nullOnDelete();
            $table->string('language', 10)->default('id');
            $table->longText('full_text');
            $table->json('segments')->nullable();
            $table->string('ai_model', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('meeting_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->string('participant_name', 255);
            $table->string('participant_email', 255)->nullable();
            $table->timestamp('join_time')->nullable();
            $table->timestamp('leave_time')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_attendance_logs');
        Schema::dropIfExists('meeting_transcripts');
        Schema::dropIfExists('meeting_recordings');
    }
};
