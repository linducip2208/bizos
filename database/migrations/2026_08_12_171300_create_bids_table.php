<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->string('bid_number');
            $table->enum('status', [
                'draft',
                'submitted',
                'shortlisted',
                'accepted',
                'rejected',
            ])->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->decimal('total_amount', 18, 2)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->integer('delivery_lead_time_days')->nullable();
            $table->integer('validity_days')->default(30);
            $table->text('notes')->nullable();
            $table->json('documents')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('evaluated_at')->nullable();
            $table->decimal('evaluation_score', 5, 2)->nullable();
            $table->text('evaluation_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
