<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('label', 255);
            $table->enum('field_type', ['text', 'textarea', 'number', 'email', 'phone', 'date', 'time', 'file', 'select', 'multiselect', 'checkbox', 'radio', 'rating', 'signature']);
            $table->string('placeholder', 255)->nullable();
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->string('validation_rules', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
