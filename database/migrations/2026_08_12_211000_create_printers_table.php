<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name', 100);
            $table->enum('printer_type', ['thermal_58', 'thermal_80', 'a4'])->default('thermal_58');
            $table->enum('connection_type', ['usb', 'network', 'cloud'])->default('usb');
            $table->string('ip_address', 45)->nullable();
            $table->integer('port')->nullable()->default(9100);
            $table->integer('paper_width')->default(58);
            $table->integer('character_per_line')->default(32);
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
