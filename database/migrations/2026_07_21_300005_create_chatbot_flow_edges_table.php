<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_flow_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flow_id')->constrained('chatbot_flows')->cascadeOnDelete();
            $table->foreignId('source_node_id')->constrained('chatbot_flow_nodes')->cascadeOnDelete();
            $table->foreignId('target_node_id')->constrained('chatbot_flow_nodes')->cascadeOnDelete();
            $table->json('condition')->nullable();
            $table->string('label', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_flow_edges');
    }
};
