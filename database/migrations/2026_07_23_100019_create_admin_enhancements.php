<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->text('license_key_encrypted');
            $table->string('module')->comment('modul yang dilisensikan');
            $table->integer('seats')->default(10);
            $table->date('started_at');
            $table->date('expires_at')->nullable();
            $table->enum('status', ['active', 'expired', 'suspended'])->default('active');
            $table->timestamps();

            $table->index(['company_id', 'module']);
            $table->index(['company_id', 'status']);
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->json('module_access')->nullable()->after('features');
            $table->boolean('white_label')->default(false)->after('module_access');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['module_access', 'white_label']);
        });

        Schema::dropIfExists('licenses');
    }
};
