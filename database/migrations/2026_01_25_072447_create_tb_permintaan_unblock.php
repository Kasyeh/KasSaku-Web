<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbPermintaanUnblock extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tb_permintaan_unblock', function (Blueprint $table) {
            $table->id();
            $table->integer('id_user');
            $table->text('pesan')->nullable();
            $table->string('status')->default('pending'); // pending, dikabulkan, ditolak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_permintaan_unblock');
    }
}
