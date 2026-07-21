<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_apps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('developer');
            $table->string('version', 20)->default('1.0.0');
            $table->string('price_type', 20)->default('free');
            $table->decimal('price', 15, 2)->default(0);
            $table->string('category')->nullable();
            $table->string('icon')->nullable();
            $table->json('screenshots')->nullable();
            $table->json('features')->nullable();
            $table->json('requirements')->nullable();
            $table->string('package_path')->nullable();
            $table->string('migration_class')->nullable();
            $table->string('seeder_class')->nullable();
            $table->json('permissions_required')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('total_installs')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('marketplace_installs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_app_id')->constrained('marketplace_apps')->restrictOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('installed_version', 20);
            $table->string('status', 20)->default('active');
            $table->date('subscription_start')->nullable();
            $table->date('subscription_end')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['marketplace_app_id', 'company_id'], 'unique_app_company_install');
        });

        Schema::create('marketplace_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_app_id')->constrained('marketplace_apps')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('rating')->default(5);
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique(['marketplace_app_id', 'company_id', 'user_id'], 'unique_app_review');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_reviews');
        Schema::dropIfExists('marketplace_installs');
        Schema::dropIfExists('marketplace_apps');
    }
};
