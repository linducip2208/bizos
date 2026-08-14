<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->enum('discount_type', ['percentage', 'fixed'])->nullable()->after('type');
            $table->decimal('discount_value', 15, 2)->nullable()->after('discount_type');
            $table->decimal('min_purchase', 20, 2)->default(0)->after('discount_value');
            $table->enum('applies_to', ['all', 'products', 'category'])->default('all')->after('min_purchase');
            $table->json('applies_to_ids')->nullable()->after('applies_to');
            $table->boolean('auto_apply')->default(false)->after('applies_to_ids');
            $table->boolean('stacking_allowed')->default(false)->after('auto_apply');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE promotions MODIFY COLUMN type ENUM('discount_percent','discount_amount','buy_x_get_y','free_shipping','bundle') DEFAULT 'discount_percent'");
        }
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value', 'min_purchase', 'applies_to', 'applies_to_ids', 'auto_apply', 'stacking_allowed']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE promotions MODIFY COLUMN type ENUM('discount_percent','discount_amount','buy_x_get_y','free_shipping') DEFAULT 'discount_percent'");
        }
    }
};
