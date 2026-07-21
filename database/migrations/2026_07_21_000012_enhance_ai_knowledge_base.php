<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_knowledge_base', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_knowledge_base', 'chunks_json')) {
                $table->json('chunks_json')->nullable()->after('embedding_vector');
            }
            if (!Schema::hasColumn('ai_knowledge_base', 'source_path')) {
                $table->string('source_path', 500)->nullable()->after('source_type');
            }
        });

        DB::statement("ALTER TABLE `ai_knowledge_base` MODIFY `source_type` VARCHAR(100) DEFAULT 'text'");
    }

    public function down(): void
    {
        Schema::table('ai_knowledge_base', function (Blueprint $table) {
            $table->dropColumn(['chunks_json', 'source_path']);
        });
    }
};
