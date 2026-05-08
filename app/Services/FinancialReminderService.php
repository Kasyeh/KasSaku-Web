<?php

namespace App\Services;

use App\Models\BudgetKategoriModel;
use App\Models\ImpianModel;
use App\Models\ImpianSetoranModel;
use App\Models\NotificationPreference;
use App\Models\TransactionModel;
use App\Models\User;
use Carbon\Carbon;

class FinancialReminderService
{
    public function dispatchForCurrentHour(?Carbon $now = null): array
    {
        $now = $now ?: now();
        $summary = [
            'processed' => 0,
            'sent' => 0,
            'daily' => 0,
            'budget' => 0,
            'dream' => 0,
        ];

        User::query()
            ->where('role', 'user')
            ->where('active', 1)
            ->whereNotNull('fcm_token')
            ->with('notificationPreference')
            ->chunkById(100, function ($users) use (&$summary, $now) {
                foreach ($users as $user) {
                    $summary['processed']++;

                    $preference = $this->resolvePreference($user);

                    if (!$preference->reminders_enabled) {
                        continue;
                    }

                    $dailySent = $this->dispatchDailyReminder($user, $preference, $now);
                    $budgetSent = $this->dispatchBudgetReminder($user, $preference, $now);
                    $dreamSent = $this->dispatchDreamReminder($user, $preference, $now);

                    $summary['daily'] += $dailySent ? 1 : 0;
                    $summary['budget'] += $budgetSent ? 1 : 0;
                    $summary['dream'] += $dreamSent ? 1 : 0;
                    $summary['sent'] += ($dailySent ? 1 : 0) + ($budgetSent ? 1 : 0) + ($dreamSent ? 1 : 0);
                }
            }, 'id_user', 'id_user');

        return $summary;
    }

    public function resolvePreference(User $user): NotificationPreference
    {
        $preference = $user->notificationPreference;

        if ($preference) {
            return $preference;
        }

        $preference = NotificationPreference::firstOrCreate(
            ['id_user' => $user->id_user],
            NotificationPreference::defaults()
        );

        $user->setRelation('notificationPreference', $preference);

        return $preference;
    }

    private function dispatchDailyReminder(User $user, NotificationPreference $preference, Carbon $now): bool
    {
        if (!$preference->daily_reminder_enabled || (int) $preference->daily_reminder_hour !== (int) $now->hour) {
            return false;
        }

        if ($preference->last_daily_reminder_sent_at && $preference->last_daily_reminder_sent_at->isSameDay($now)) {
            return false;
        }

        $hasTransactionToday = TransactionModel::where('id_user', $user->id_user)
            ->whereDate('tanggal', $now->toDateString())
            ->exists();

        if ($hasTransactionToday) {
            return false;
        }

        $sent = app(FirebaseService::class)->sendFinancialReminderNotification(
            $user,
            'daily_input',
            'Pengingat catatan harian',
            'Belum ada transaksi yang dicatat hari ini. Luangkan 1 menit untuk update pemasukan atau pengeluaran Anda.',
            [
                'scheduled_hour' => (string) $preference->daily_reminder_hour,
            ]
        );

        if ($sent) {
            $preference->forceFill([
                'last_daily_reminder_sent_at' => $now->copy(),
            ])->save();
        }

        return $sent;
    }

    private function dispatchBudgetReminder(User $user, NotificationPreference $preference, Carbon $now): bool
    {
        if (!$preference->budget_alert_enabled) {
            return false;
        }

        $budgets = BudgetKategoriModel::where('id_user', $user->id_user)->get();
        $candidate = null;

        foreach ($budgets as $budget) {
            $spent = $budget->getSpentAmount();
            $limit = (float) $budget->nominal;

            if ($limit <= 0) {
                continue;
            }

            $percentage = round(($spent / $limit) * 100, 2);
            $status = null;

            if ($spent > $limit) {
                $status = 'exceeded';
            } elseif ($percentage >= (int) $preference->budget_alert_threshold) {
                $status = 'warning';
            }

            if (!$status) {
                continue;
            }

            $score = $status === 'exceeded' ? 200 + $percentage : 100 + $percentage;
            if (!$candidate || $score > $candidate['score']) {
                $candidate = [
                    'budget' => $budget,
                    'spent' => $spent,
                    'limit' => $limit,
                    'percentage' => min(999, $percentage),
                    'status' => $status,
                    'score' => $score,
                ];
            }
        }

        if (!$candidate) {
            return false;
        }

        $alertKey = implode('|', [
            $now->format('Y-m-d'),
            $candidate['budget']->id,
            $candidate['status'],
        ]);

        if ($preference->last_budget_alert_sent_key === $alertKey) {
            return false;
        }

        $title = $candidate['status'] === 'exceeded' ? 'Budget terlampaui' : 'Budget hampir habis';
        $body = sprintf(
            'Kategori %s sudah memakai Rp %s dari Rp %s (%s%%).',
            ucfirst($candidate['budget']->kategori),
            number_format($candidate['spent'], 0, ',', '.'),
            number_format($candidate['limit'], 0, ',', '.'),
            rtrim(rtrim(number_format($candidate['percentage'], 2, '.', ''), '0'), '.')
        );

        $sent = app(FirebaseService::class)->sendFinancialReminderNotification(
            $user,
            'budget_alert',
            $title,
            $body,
            [
                'budget_id' => (string) $candidate['budget']->id,
                'budget_status' => $candidate['status'],
                'budget_percentage' => (string) $candidate['percentage'],
            ]
        );

        if ($sent) {
            $preference->forceFill([
                'last_budget_alert_sent_key' => $alertKey,
            ])->save();
        }

        return $sent;
    }

    private function dispatchDreamReminder(User $user, NotificationPreference $preference, Carbon $now): bool
    {
        if (!$preference->dream_reminder_enabled) {
            return false;
        }

        $inactiveSince = $now->copy()->subDays((int) $preference->dream_inactive_days);
        $dreams = ImpianModel::where('id_user', $user->id_user)
            ->orderBy('deadline', 'asc')
            ->get();

        $candidate = null;

        foreach ($dreams as $dream) {
            $target = (float) $dream->harga_barang;
            if ($target <= 0) {
                continue;
            }

            $totalSetoran = (float) ImpianSetoranModel::where('id_impian', $dream->id_impian)->sum('nominal');
            if ($totalSetoran >= $target) {
                continue;
            }

            $lastSetoran = ImpianSetoranModel::where('id_impian', $dream->id_impian)
                ->latest('tanggal')
                ->first();

            if ($lastSetoran && Carbon::parse($lastSetoran->tanggal)->greaterThan($inactiveSince)) {
                continue;
            }

            $candidate = [
                'dream' => $dream,
                'progress' => round(($totalSetoran / $target) * 100, 2),
                'remaining' => max(0, $target - $totalSetoran),
            ];
            break;
        }

        if (!$candidate) {
            return false;
        }

        $alertKey = implode('|', [
            $now->format('Y-m-d'),
            $candidate['dream']->id_impian,
        ]);

        if ($preference->last_dream_reminder_sent_key === $alertKey) {
            return false;
        }

        $body = sprintf(
            'Impian %s masih tersisa Rp %s. Sudah %d hari tanpa setoran baru.',
            $candidate['dream']->nama_barang,
            number_format($candidate['remaining'], 0, ',', '.'),
            (int) $preference->dream_inactive_days
        );

        $sent = app(FirebaseService::class)->sendFinancialReminderNotification(
            $user,
            'dream_progress',
            'Lanjutkan target impian Anda',
            $body,
            [
                'dream_id' => (string) $candidate['dream']->id_impian,
                'dream_progress' => (string) $candidate['progress'],
            ]
        );

        if ($sent) {
            $preference->forceFill([
                'last_dream_reminder_sent_key' => $alertKey,
            ])->save();
        }

        return $sent;
    }
}
