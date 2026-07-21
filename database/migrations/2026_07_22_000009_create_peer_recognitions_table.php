<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peer_recognitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('badge');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['to_user_id', 'created_at']);
            $table->index(['from_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peer_recognitions');
    }
};
