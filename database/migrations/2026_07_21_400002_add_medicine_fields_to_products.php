<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_medicine')->default(false)->after('is_active');
            $table->string('active_ingredient')->nullable()->after('is_medicine');
            $table->string('dosage_form', 50)->nullable()->after('active_ingredient');
            $table->string('strength', 100)->nullable()->after('dosage_form');
            $table->string('registration_number', 100)->nullable()->after('strength');
            $table->boolean('requires_prescription')->default(false)->after('registration_number');
            $table->string('drug_category', 50)->nullable()->after('requires_prescription');
            $table->string('storage_requirement')->nullable()->after('drug_category');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'is_medicine', 'active_ingredient', 'dosage_form', 'strength',
                'registration_number', 'requires_prescription', 'drug_category', 'storage_requirement',
            ]);
        });
    }
};
