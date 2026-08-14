<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('objective_type', ['company', 'department', 'team', 'individual'])->default('individual');
            $table->string('owner_type', 100)->nullable()->comment('Department or Employee');
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->foreignId('parent_objective_id')->nullable()->constrained('objectives')->nullOnDelete();
            $table->foreignId('cycle_id')->nullable()->constrained('performance_cycles')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'on_track', 'at_risk', 'behind', 'completed', 'cancelled'])->default('draft');
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->integer('weight')->default(100);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objectives');
    }
};
