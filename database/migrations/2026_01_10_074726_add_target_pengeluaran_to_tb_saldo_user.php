<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTargetPengeluaranToTbSaldoUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_saldo_user', function (Blueprint $table) {
            $table->decimal('target_pengeluaran', 15, 2)->nullable()->default(null)->after('pengeluaran');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_saldo_user', function (Blueprint $table) {
            $table->dropColumn('target_pengeluaran');
        });
    }
}
