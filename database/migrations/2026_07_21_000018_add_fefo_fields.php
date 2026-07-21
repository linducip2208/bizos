<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_balances', function (Blueprint $table) {
            $table->string('lot_number', 100)->nullable()->after('last_cost');
            $table->date('manufacturing_date')->nullable()->after('lot_number');
            $table->date('expiry_date')->nullable()->after('manufacturing_date');
        });

        Schema::create('abc_classifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('classification', 1);
            $table->decimal('annual_consumption_value', 20, 2);
            $table->decimal('cumulative_percent', 8, 2);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abc_classifications');

        Schema::table('stock_balances', function (Blueprint $table) {
            $table->dropColumn(['lot_number', 'manufacturing_date', 'expiry_date']);
        });
    }
};
