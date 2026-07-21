<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->enum('tax_type', ['ppn', 'pph21', 'pph22', 'pph23', 'pph25', 'pph29', 'pph_final']);
            $table->string('name', 255);
            $table->decimal('rate', 5, 4);
            $table->smallInteger('effective_year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_configs');
    }
};
