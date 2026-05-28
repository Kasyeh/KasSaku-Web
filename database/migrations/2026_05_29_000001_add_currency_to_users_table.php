<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds currency preference columns to the users table.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('currency', 10)->default('IDR')->after('avatar');
            $table->string('currency_format', 20)->default('standard')->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['currency', 'currency_format']);
        });
    }
};
