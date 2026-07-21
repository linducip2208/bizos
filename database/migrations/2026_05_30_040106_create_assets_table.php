<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('asset_categories')->restrictOnDelete();
            $table->string('asset_code', 50)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 20, 2);
            $table->integer('useful_life_years');
            $table->decimal('salvage_value', 20, 2)->default(0);
            $table->decimal('current_value', 20, 2);
            $table->decimal('accumulated_depreciation', 20, 2)->default(0);
            $table->string('location', 255)->nullable();
            $table->foreignId('current_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('status', ['active', 'maintenance', 'disposed', 'sold', 'written_off'])->default('active');
            $table->foreignId('purchase_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->date('warranty_expiry')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
