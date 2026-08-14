<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 100);
            $table->enum('type', ['pos_receipt', 'invoice', 'label'])->default('pos_receipt');
            $table->text('header_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_qr')->default(false);
            $table->boolean('show_tax_summary')->default(true);
            $table->boolean('show_payment_summary')->default(true);
            $table->enum('font_size', ['small', 'medium', 'large'])->default('medium');
            $table->json('layout_config')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_layouts');
    }
};
