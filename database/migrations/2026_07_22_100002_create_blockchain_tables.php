<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blockchain_ledger', function (Blueprint $table) {
            $table->id();
            $table->integer('block_number');
            $table->string('previous_hash', 64);
            $table->string('block_hash', 64);
            $table->json('data');
            $table->integer('nonce')->default(0);
            $table->timestamp('mined_at');
            $table->timestamps();

            $table->unique('block_number');
            $table->unique('block_hash');
        });

        Schema::create('blockchain_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->nullable()->constrained('blockchain_ledger')->nullOnDelete();
            $table->string('transaction_hash', 64)->unique();
            $table->string('type', 50)->comment('document_notarization / certificate_issuance / smart_contract / supply_chain_event');
            $table->morphs('reference');
            $table->string('document_hash', 64)->nullable();
            $table->string('file_name', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('timestamped_at');
            $table->timestamps();
        });

        Schema::create('product_blockchain_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('blockchain_transactions')->nullOnDelete();
            $table->string('event_type', 50)->comment('manufactured / qc_passed / shipped / received / sold / returned');
            $table->json('event_data')->nullable();
            $table->string('location', 255)->nullable();
            $table->string('actor_name', 255)->nullable();
            $table->string('document_hash', 64)->nullable();
            $table->integer('block_number')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_blockchain_events');
        Schema::dropIfExists('blockchain_transactions');
        Schema::dropIfExists('blockchain_ledger');
    }
};
