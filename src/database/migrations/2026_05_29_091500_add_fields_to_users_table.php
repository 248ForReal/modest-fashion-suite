<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('name');
            $table->date('birth_date')->nullable()->after('phone');
            $table->string('google_id')->nullable()->unique()->after('birth_date');
            $table->string('avatar')->nullable()->after('google_id');
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending')->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'birth_date', 'google_id', 'avatar', 'status']);
        });
    }
};
