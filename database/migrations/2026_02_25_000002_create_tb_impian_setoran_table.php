<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('tb_impian_setoran')) {
            Schema::create('tb_impian_setoran', function (Blueprint $table) {
                $table->bigIncrements('id_setoran_impian');
                $table->unsignedBigInteger('id_impian');
                $table->unsignedBigInteger('id_user');
                $table->unsignedBigInteger('nominal');
                $table->string('keterangan', 255)->nullable();
                $table->dateTime('tanggal');
                $table->timestamps();

                $table->index(['id_impian', 'tanggal'], 'tb_impian_setoran_impian_tanggal_idx');
                $table->index(['id_user', 'tanggal'], 'tb_impian_setoran_user_tanggal_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tb_impian_setoran')) {
            Schema::dropIfExists('tb_impian_setoran');
        }
    }
};
