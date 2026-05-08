<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tb_budget_kategori', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->string('kategori', 100);
            $table->decimal('nominal', 15, 2);
            $table->enum('periode', ['mingguan', 'bulanan', 'custom'])->default('bulanan');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_akhir')->nullable();
            $table->timestamps();

            $table->unique(['id_user', 'kategori', 'periode', 'tanggal_mulai'], 'budget_kategori_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_budget_kategori');
    }
};
