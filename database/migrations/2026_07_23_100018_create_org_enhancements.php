<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->foreignId('parent_id')->nullable()->constrained('business_units')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('business_unit_id')->nullable()->constrained('business_units')->nullOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('parent_id')->constrained('sections')->nullOnDelete();
            $table->foreignId('business_unit_id')->nullable()->after('section_id')->constrained('business_units')->nullOnDelete();
        });

        Schema::create('employment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->date('date');
            $table->enum('type', ['national', 'company', 'religious'])->default('company');
            $table->boolean('is_recurring')->default(false);
            $table->integer('year')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'date']);
        });

        Schema::create('work_calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->integer('year');
            $table->json('config')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'year', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropForeign(['business_unit_id']);
            $table->dropColumn(['section_id', 'business_unit_id']);
        });

        Schema::dropIfExists('work_calendars');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('employment_types');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('divisions');
        Schema::dropIfExists('business_units');
    }
};
