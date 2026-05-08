<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbNotificationPreferences extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tb_notification_preferences')) {
            Schema::create('tb_notification_preferences', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('id_user')->unique();
                $table->boolean('reminders_enabled')->default(true);
                $table->boolean('daily_reminder_enabled')->default(true);
                $table->unsignedTinyInteger('daily_reminder_hour')->default(20);
                $table->boolean('budget_alert_enabled')->default(true);
                $table->unsignedTinyInteger('budget_alert_threshold')->default(80);
                $table->boolean('dream_reminder_enabled')->default(true);
                $table->unsignedTinyInteger('dream_inactive_days')->default(7);
                $table->dateTime('last_daily_reminder_sent_at')->nullable();
                $table->string('last_budget_alert_sent_key')->nullable();
                $table->string('last_dream_reminder_sent_key')->nullable();
                $table->timestamps();

                $table->index('reminders_enabled', 'tb_notification_preferences_enabled_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tb_notification_preferences')) {
            Schema::dropIfExists('tb_notification_preferences');
        }
    }
}
