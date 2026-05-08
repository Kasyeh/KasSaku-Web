<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixBalanceColumnTypesInTbSaldoUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        \DB::statement('ALTER TABLE tb_saldo_user MODIFY pemasukan DECIMAL(20,2) DEFAULT 0.00');
        \DB::statement('ALTER TABLE tb_saldo_user MODIFY pengeluaran DECIMAL(20,2) DEFAULT 0.00');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement('ALTER TABLE tb_saldo_user MODIFY pemasukan VARCHAR(11) DEFAULT "0"');
        \DB::statement('ALTER TABLE tb_saldo_user MODIFY pengeluaran VARCHAR(11) DEFAULT "0"');
    }
}
