<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->date('record_date');
            $table->string('waste_type'); // hazardous, solid, liquid, organic, recyclable, electronic
            $table->string('waste_subtype')->nullable();
            $table->decimal('quantity_kg', 12, 3);
            $table->string('source')->nullable(); // production, office, canteen, warehouse, construction
            $table->string('disposal_method'); // landfill, incinerated, recycled, composted, treated_offsite
            $table->string('disposal_vendor')->nullable();
            $table->decimal('disposal_cost', 15, 2)->nullable();
            $table->boolean('is_hazardous')->default(false);
            $table->string('manifest_number')->nullable(); // hazardous waste manifest
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_records');
    }
};
