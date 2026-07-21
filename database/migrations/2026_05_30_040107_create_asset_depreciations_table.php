<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->decimal('depreciation_amount', 20, 2);
            $table->decimal('accumulated_before', 20, 2);
            $table->decimal('accumulated_after', 20, 2);
            $table->decimal('book_value_after', 20, 2);
            $table->foreignId('journal_id')->nullable()->constrained('journals')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
    }
};
