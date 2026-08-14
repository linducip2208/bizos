<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('short_code', 20)->nullable();
            $table->enum('type', ['dine_in', 'takeaway', 'delivery', 'other'])->default('dine_in');
            $table->decimal('pack_price', 20, 2)->nullable();
            $table->enum('pack_charge_type', ['percentage', 'fixed'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_types');
    }
};
