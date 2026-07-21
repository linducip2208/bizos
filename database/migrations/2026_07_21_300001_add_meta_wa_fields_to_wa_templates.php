<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wa_templates', function (Blueprint $table) {
            $table->string('meta_template_id', 100)->nullable()->after('status')->comment('Meta WA template ID');
            $table->string('meta_template_status', 50)->nullable()->after('meta_template_id')->comment('Meta approval status');
            $table->text('meta_rejection_reason')->nullable()->after('meta_template_status');
            $table->json('components')->nullable()->after('content')->comment('Meta template components (header, body, footer, buttons)');
            $table->string('quality_score', 10)->nullable()->after('meta_rejection_reason');
            $table->timestamp('meta_synced_at')->nullable()->after('quality_score');
            $table->timestamp('rejected_at')->nullable()->after('meta_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('wa_templates', function (Blueprint $table) {
            $table->dropColumn([
                'meta_template_id',
                'meta_template_status',
                'meta_rejection_reason',
                'components',
                'quality_score',
                'meta_synced_at',
                'rejected_at',
            ]);
        });
    }
};
