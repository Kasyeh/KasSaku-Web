<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $table = 'tb_notification_preferences';

    protected $fillable = [
        'id_user',
        'reminders_enabled',
        'daily_reminder_enabled',
        'daily_reminder_hour',
        'budget_alert_enabled',
        'budget_alert_threshold',
        'dream_reminder_enabled',
        'dream_inactive_days',
        'last_daily_reminder_sent_at',
        'last_budget_alert_sent_key',
        'last_dream_reminder_sent_key',
    ];

    protected $casts = [
        'reminders_enabled' => 'boolean',
        'daily_reminder_enabled' => 'boolean',
        'daily_reminder_hour' => 'integer',
        'budget_alert_enabled' => 'boolean',
        'budget_alert_threshold' => 'integer',
        'dream_reminder_enabled' => 'boolean',
        'dream_inactive_days' => 'integer',
        'last_daily_reminder_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public static function defaults(): array
    {
        return [
            'reminders_enabled' => true,
            'daily_reminder_enabled' => true,
            'daily_reminder_hour' => 20,
            'budget_alert_enabled' => true,
            'budget_alert_threshold' => 80,
            'dream_reminder_enabled' => true,
            'dream_inactive_days' => 7,
        ];
    }
}
