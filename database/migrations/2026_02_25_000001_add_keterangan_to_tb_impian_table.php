<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKeteranganToTbImpianTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tb_impian') && !Schema::hasColumn('tb_impian', 'keterangan')) {
            Schema::table('tb_impian', function (Blueprint $table) {
                $table->string('keterangan', 255)->nullable()->after('deadline');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('tb_impian') && Schema::hasColumn('tb_impian', 'keterangan')) {
            Schema::table('tb_impian', function (Blueprint $table) {
                $table->dropColumn('keterangan');
            });
        }
    }
}

