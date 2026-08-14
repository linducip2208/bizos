<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('key_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('objectives')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('metric_type', ['percentage', 'number', 'currency', 'boolean', 'milestone'])->default('number');
            $table->decimal('start_value', 15, 4)->default(0);
            $table->decimal('current_value', 15, 4)->default(0);
            $table->decimal('target_value', 15, 4);
            $table->string('unit', 100)->nullable();
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->enum('status', ['draft', 'active', 'on_track', 'at_risk', 'behind', 'completed', 'cancelled'])->default('draft');
            $table->decimal('weight', 5, 2)->default(100);
            $table->date('due_date')->nullable();
            $table->enum('check_in_frequency', ['weekly', 'biweekly', 'monthly', 'quarterly'])->default('monthly');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('key_results');
    }
};
