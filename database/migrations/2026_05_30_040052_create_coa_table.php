<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('coa_categories')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('coa')->nullOnDelete();
            $table->string('code', 30);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_header')->default(false);
            $table->decimal('opening_balance', 20, 2)->default(0);
            $table->enum('balance_type', ['debit', 'credit']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa');
    }
};
