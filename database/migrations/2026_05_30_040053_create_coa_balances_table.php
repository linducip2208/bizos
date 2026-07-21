<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coa_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coa_id')->constrained('coa')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('opening_balance', 20, 2)->default(0);
            $table->decimal('debit_total', 20, 2)->default(0);
            $table->decimal('credit_total', 20, 2)->default(0);
            $table->decimal('closing_balance', 20, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coa_balances');
    }
};
