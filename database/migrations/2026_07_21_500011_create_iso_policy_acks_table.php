<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iso_policy_acks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iso_policy_id')->constrained('iso_policies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->useCurrent();
            $table->string('ip_address')->nullable();
            $table->string('signature_type')->default('digital'); // digital, click_wrap, written
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['iso_policy_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iso_policy_acks');
    }
};
