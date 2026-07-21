<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_members', function (Blueprint $table) {
            $table->integer('points_balance')->default(0)->after('total_spent');
            $table->enum('tier', ['silver', 'gold', 'platinum'])->default('silver')->after('points_balance');
            $table->integer('total_points_earned')->default(0)->after('tier');
            $table->date('birthday')->nullable()->after('total_points_earned');
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('pos_members')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('pos_transactions')->nullOnDelete();
            $table->enum('type', ['earn', 'redeem', 'expire', 'adjustment']);
            $table->integer('points');
            $table->string('description', 500)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('loyalty_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->decimal('earn_rate', 10, 2)->default(1.00);
            $table->decimal('redeem_rate', 10, 2)->default(100.00);
            $table->integer('points_expiry_months')->default(12);
            $table->integer('silver_threshold')->default(0);
            $table->integer('gold_threshold')->default(5000);
            $table->integer('platinum_threshold')->default(20000);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_configs');
        Schema::dropIfExists('loyalty_transactions');

        Schema::table('pos_members', function (Blueprint $table) {
            $table->dropColumn(['points_balance', 'tier', 'total_points_earned', 'birthday']);
        });
    }
};
