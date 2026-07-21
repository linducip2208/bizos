<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_payment_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_payment_id')->constrained('pos_payments')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_method_name', 100)->nullable();
            $table->decimal('amount', 20, 2);
            $table->string('reference_number', 100)->nullable();
            $table->string('approval_code', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_payment_lines');
    }
};
