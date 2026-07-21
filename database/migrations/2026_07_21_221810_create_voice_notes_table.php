<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('audio_path');
            $table->integer('duration_seconds')->default(0);
            $table->text('transcript')->nullable();
            $table->string('title')->nullable();
            $table->string('context_type')->nullable();
            $table->unsignedBigInteger('context_id')->nullable();
            $table->timestamps();

            $table->index(['sender_id', 'created_at']);
            $table->index(['context_type', 'context_id']);
        });

        Schema::create('voice_note_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voice_note_id')->constrained('voice_notes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_played')->default(false);
            $table->timestamp('played_at')->nullable();
            $table->timestamps();

            $table->unique(['voice_note_id', 'user_id']);
            $table->index(['user_id', 'is_played']);
        });

        Schema::create('voice_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('voice_channel_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voice_channel_id')->constrained('voice_channels')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();

            $table->unique(['voice_channel_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_channel_members');
        Schema::dropIfExists('voice_channels');
        Schema::dropIfExists('voice_note_recipients');
        Schema::dropIfExists('voice_notes');
    }
};
