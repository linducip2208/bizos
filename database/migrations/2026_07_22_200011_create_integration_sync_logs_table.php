<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('integration_connector_id')->nullable()->constrained('integration_connectors')->nullOnDelete();
            $table->string('connector_type');
            $table->string('entity'); // invoices, payments, contacts, journal_entries, calendar, bank_transactions, efaktur
            $table->string('direction'); // inbound, outbound, bidirectional
            $table->string('status'); // pending, running, success, partial, failed
            $table->integer('records_processed')->default(0);
            $table->integer('records_succeeded')->default(0);
            $table->integer('records_failed')->default(0);
            $table->json('error_details')->nullable();
            $table->text('summary')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_sync_logs');
    }
};
