<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained('calendars')->cascadeOnDelete();
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->boolean('is_all_day')->default(false);
            $table->string('location', 255)->nullable();
            $table->string('color', 20)->nullable();
            $table->string('eventable_type', 100)->nullable();
            $table->unsignedBigInteger('eventable_id')->nullable();
            $table->timestamps();

            $table->index(['eventable_type', 'eventable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
