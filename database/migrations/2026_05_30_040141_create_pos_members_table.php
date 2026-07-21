<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('member_code', 50)->unique();
            $table->string('name', 255);
            $table->string('phone', 30)->nullable();
            $table->string('email', 255)->nullable();
            $table->integer('points')->default(0);
            $table->decimal('total_spent', 20, 2)->default(0);
            $table->date('join_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_members');
    }
};
