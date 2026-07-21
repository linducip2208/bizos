<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pph21_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->enum('ptkp_category', ['tk0', 'tk1', 'tk2', 'tk3', 'k0', 'k1', 'k2', 'k3']);
            $table->decimal('ptkp_amount', 20, 2);
            $table->decimal('threshold_low', 20, 2);
            $table->decimal('rate_low', 5, 4);
            $table->decimal('threshold_mid', 20, 2);
            $table->decimal('rate_mid', 5, 4);
            $table->decimal('threshold_high', 20, 2);
            $table->decimal('rate_high', 5, 4);
            $table->decimal('rate_top', 5, 4);
            $table->smallInteger('effective_year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pph21_configs');
    }
};
