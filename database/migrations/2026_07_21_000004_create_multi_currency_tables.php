<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name', 100);
            $table->string('symbol', 10);
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);
            $table->boolean('is_base')->default(false);
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('decimal_places')->default(2);
            $table->string('thousands_separator', 1)->default('.');
            $table->string('decimal_separator', 1)->default(',');
            $table->string('format', 20)->default('1.234,56');
            $table->timestamps();
        });

        Schema::create('exchange_rate_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->date('rate_date');
            $table->decimal('rate', 15, 6);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rate_logs');
        Schema::dropIfExists('currencies');
    }
};
