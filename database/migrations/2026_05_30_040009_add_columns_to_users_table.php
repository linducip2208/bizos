<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar', 255)->nullable()->after('password');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->unsignedBigInteger('employee_id')->nullable()->after('last_login_ip');
            $table->foreignId('company_id')->nullable()->after('employee_id')->constrained('companies')->nullOnDelete();
            $table->unsignedBigInteger('role_id')->nullable()->after('company_id');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['company_id']);
            $table->dropColumn([
                'avatar',
                'is_active',
                'last_login_at',
                'last_login_ip',
                'employee_id',
                'company_id',
                'role_id',
            ]);
        });
    }
};
