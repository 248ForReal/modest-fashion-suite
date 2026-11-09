<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employment_type', 10)->default('full_time')->after('status');
            $table->string('bank_name')->nullable()->after('employment_type');
            $table->string('bank_account', 50)->nullable()->after('bank_name');
            $table->unsignedBigInteger('default_allowance')->default(0)->after('bank_account');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['employment_type', 'bank_name', 'bank_account', 'default_allowance']);
        });
    }
};
