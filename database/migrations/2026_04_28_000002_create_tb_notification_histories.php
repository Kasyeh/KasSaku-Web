<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbNotificationHistories extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_notification_histories')) {
            Schema::create('tb_notification_histories', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('id_user');
                $table->string('category', 50);
                $table->string('title', 150);
                $table->text('body');
                $table->text('payload')->nullable();
                $table->dateTime('sent_at');
                $table->dateTime('read_at')->nullable();
                $table->timestamps();

                $table->index(['id_user', 'sent_at'], 'tb_notification_histories_user_sent_idx');
                $table->index(['id_user', 'read_at'], 'tb_notification_histories_user_read_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_notification_histories')) {
            Schema::dropIfExists('tb_notification_histories');
        }
    }
}
