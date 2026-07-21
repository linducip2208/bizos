<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('work_center_id')->nullable()->constrained('work_centers')->nullOnDelete();
            $table->string('name');
            $table->string('model')->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->decimal('capacity_per_hour', 10, 2)->default(0);
            $table->enum('status', ['active', 'maintenance', 'broken'])->default('active');
            $table->timestamps();
        });

        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('planned_quantity', 12, 4);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'confirmed', 'in_progress', 'completed'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('finished_goods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->timestamp('accepted_at')->nullable();
            $table->enum('quality_status', ['passed', 'failed', 'rework'])->default('passed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->foreignId('machine_id')->nullable()->after('work_center_id')->constrained('machines')->nullOnDelete();
            $table->foreignId('production_plan_id')->nullable()->after('machine_id')->constrained('production_plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropForeign(['production_plan_id']);
            $table->dropForeign(['machine_id']);
            $table->dropColumn(['production_plan_id', 'machine_id']);
        });

        Schema::dropIfExists('finished_goods');
        Schema::dropIfExists('production_plans');
        Schema::dropIfExists('machines');
    }
};
