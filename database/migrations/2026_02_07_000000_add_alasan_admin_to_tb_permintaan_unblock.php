<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAlasanAdminToTbPermintaanUnblock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tb_permintaan_unblock', function (Blueprint $table) {
            $table->text('alasan_admin')->nullable()->after('pesan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tb_permintaan_unblock', function (Blueprint $table) {
            $table->dropColumn('alasan_admin');
        });
    }
}
