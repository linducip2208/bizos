<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consent_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('person_type'); // employee, client, supplier
            $table->unsignedBigInteger('person_id');
            $table->string('purpose'); // marketing, data_sharing, biometric, location, analytics
            $table->string('method'); // written, electronic, implied, verbal
            $table->timestamp('consented_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->text('withdrawal_reason')->nullable();
            $table->text('scope_description')->nullable();
            $table->string('status')->default('active'); // active, withdrawn, expired
            $table->text('metadata')->nullable();
            $table->timestamps();

            $table->index(['person_type', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consent_records');
    }
};
