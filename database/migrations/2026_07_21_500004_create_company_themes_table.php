<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name')->default('Default');
            $table->string('primary_color', 20)->default('#4f46e5');
            $table->string('secondary_color', 20)->default('#7c3aed');
            $table->string('accent_color', 20)->default('#2dd4bf');
            $table->string('background_color', 20)->default('#f8fafc');
            $table->string('text_color', 20)->default('#1e293b');
            $table->string('font_family')->default('Inter');
            $table->integer('border_radius')->default(12);
            $table->string('button_style', 20)->default('rounded');
            $table->string('sidebar_style', 20)->default('default');
            $table->json('dark_mode_colors')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->text('custom_css')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('companies', function (Blueprint $table) {
            if (!Schema::hasColumn('companies', 'active_theme_id')) {
                $table->foreignId('active_theme_id')->nullable()->after('sandbox_source_id')
                    ->constrained('company_themes')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (Schema::hasColumn('companies', 'active_theme_id')) {
                $table->dropConstrainedForeignId('active_theme_id');
            }
        });
        Schema::dropIfExists('company_themes');
    }
};
