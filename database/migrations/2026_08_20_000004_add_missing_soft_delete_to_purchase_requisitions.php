<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('purchase_requisitions', 'deleted_at')) {
            Schema::table('purchase_requisitions', fn (Blueprint $table) => $table->softDeletes());
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('purchase_requisitions', 'deleted_at')) {
            Schema::table('purchase_requisitions', fn (Blueprint $table) => $table->dropSoftDeletes());
        }
    }
};
