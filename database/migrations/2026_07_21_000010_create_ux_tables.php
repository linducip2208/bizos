<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('resource_type');
            $table->string('resource_label');
            $table->string('resource_url');
            $table->string('resource_icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'resource_type']);
        });

        Schema::create('recently_viewed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('resource_type');
            $table->string('resource_label');
            $table->string('resource_url');
            $table->string('resource_icon')->nullable();
            $table->timestamp('viewed_at')->useCurrent();

            $table->index(['user_id', 'viewed_at']);
            $table->index(['user_id', 'resource_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recently_viewed');
        Schema::dropIfExists('user_favorites');
    }
};
