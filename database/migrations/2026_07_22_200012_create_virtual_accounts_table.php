<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('virtual_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('va_number');
            $table->string('bank'); // bca, mandiri, bri, bni, cimb
            $table->string('name');
            $table->decimal('expected_amount', 15, 2)->nullable();
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, active, paid, expired, closed
            $table->timestamp('expiry_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('paid_by')->nullable();
            $table->json('metadata')->nullable();
            $table->string('reference_entity')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();

            $table->unique(['bank', 'va_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('virtual_accounts');
    }
};
