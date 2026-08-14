<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('key_result_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('key_result_id')->constrained('key_results')->cascadeOnDelete();
            $table->decimal('value', 15, 4);
            $table->text('notes')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->tinyInteger('confidence_level')->nullable()->comment('Skala 1-5');
            $table->boolean('is_on_track')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('key_result_check_ins');
    }
};
