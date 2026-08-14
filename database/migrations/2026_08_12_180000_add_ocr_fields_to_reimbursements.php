<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->string('receipt_image_path')->nullable()->after('description');
            $table->json('ocr_data')->nullable()->after('receipt_image_path');
            $table->decimal('ocr_confidence', 5, 2)->nullable()->default(null)->after('ocr_data');
            $table->enum('ocr_status', ['pending', 'processing', 'completed', 'failed'])->nullable()->after('ocr_confidence');
        });
    }

    public function down(): void
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->dropColumn(['receipt_image_path', 'ocr_data', 'ocr_confidence', 'ocr_status']);
        });
    }
};
