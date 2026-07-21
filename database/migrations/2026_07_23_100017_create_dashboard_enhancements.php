<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('action', 50)->comment('created, updated, deleted, status_changed, approved, rejected');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('user_favorites', function (Blueprint $table) {
            $table->integer('pin_order')->default(0)->after('sort_order');
            $table->string('section', 100)->nullable()->after('pin_order');
            $table->string('color', 20)->nullable()->after('section');
        });

        Schema::create('personal_dashboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->json('layout')->nullable();
            $table->timestamps();
        });

        Schema::create('personal_dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_dashboard_id')->constrained('personal_dashboards')->cascadeOnDelete();
            $table->string('widget_type');
            $table->json('config')->nullable();
            $table->json('position')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_dashboard_widgets');
        Schema::dropIfExists('personal_dashboards');

        Schema::table('user_favorites', function (Blueprint $table) {
            $table->dropColumn(['pin_order', 'section', 'color']);
        });

        Schema::dropIfExists('activity_timeline');
    }
};
