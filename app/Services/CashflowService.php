<?php

namespace App\Services;

use App\Models\TransactionModel;
use Carbon\Carbon;

class CashflowService
{
    public static function buildSeries(int $userId): array
    {
        $now = Carbon::now();
        $start = $now->copy()->subMonths(12)->startOfMonth();

        $transactions = TransactionModel::where('id_user', $userId)
            ->whereBetween('tanggal', [$start, $now->copy()->endOfDay()])
            ->get(['tanggal', 'tipe', 'nominal']);

        $daily = [];
        $monthly = [];

        foreach ($transactions as $trx) {
            $date = Carbon::parse($trx->tanggal);
            $dayKey = $date->toDateString();
            $monthKey = $date->format('Y-m');

            if (!isset($daily[$dayKey])) {
                $daily[$dayKey] = ['income' => 0.0, 'expense' => 0.0];
            }
            if (!isset($monthly[$monthKey])) {
                $monthly[$monthKey] = ['income' => 0.0, 'expense' => 0.0];
            }

            if ($trx->tipe === 'pemasukan') {
                $daily[$dayKey]['income'] += (float) $trx->nominal;
                $monthly[$monthKey]['income'] += (float) $trx->nominal;
            } else {
                $daily[$dayKey]['expense'] += (float) $trx->nominal;
                $monthly[$monthKey]['expense'] += (float) $trx->nominal;
            }
        }

        return [
            '7d' => self::buildDailySeries($daily, 7, $now),
            '30d' => self::buildDailySeries($daily, 30, $now),
            '3m' => self::buildMonthlySeries($monthly, 3, $now),
            '12m' => self::buildMonthlySeries($monthly, 12, $now),
        ];
    }

    private static function buildDailySeries(array $dailyMap, int $days, Carbon $now): array
    {
        $labels = [];
        $income = [];
        $expense = [];
        $net = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $key = $date->toDateString();
            $inc = $dailyMap[$key]['income'] ?? 0.0;
            $exp = $dailyMap[$key]['expense'] ?? 0.0;

            $labels[] = $date->translatedFormat('d M');
            $income[] = round($inc, 2);
            $expense[] = round($exp, 2);
            $net[] = round($inc - $exp, 2);
        }

        $currentNet = array_sum($net);
        $previousNet = self::calculatePreviousDailyNet($dailyMap, $days, $now);

        return self::finalizeSeries($labels, $income, $expense, $net, $currentNet, $previousNet);
    }

    private static function buildMonthlySeries(array $monthlyMap, int $months, Carbon $now): array
    {
        $labels = [];
        $income = [];
        $expense = [];
        $net = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $key = $date->format('Y-m');
            $inc = $monthlyMap[$key]['income'] ?? 0.0;
            $exp = $monthlyMap[$key]['expense'] ?? 0.0;

            $labels[] = $date->translatedFormat('M y');
            $income[] = round($inc, 2);
            $expense[] = round($exp, 2);
            $net[] = round($inc - $exp, 2);
        }

        $currentNet = array_sum($net);
        $previousNet = self::calculatePreviousMonthlyNet($monthlyMap, $months, $now);

        return self::finalizeSeries($labels, $income, $expense, $net, $currentNet, $previousNet);
    }

    private static function calculatePreviousDailyNet(array $dailyMap, int $days, Carbon $now): float
    {
        $sum = 0.0;
        for ($i = $days * 2 - 1; $i >= $days; $i--) {
            $date = $now->copy()->subDays($i)->toDateString();
            $inc = $dailyMap[$date]['income'] ?? 0.0;
            $exp = $dailyMap[$date]['expense'] ?? 0.0;
            $sum += ($inc - $exp);
        }
        return $sum;
    }

    private static function calculatePreviousMonthlyNet(array $monthlyMap, int $months, Carbon $now): float
    {
        $sum = 0.0;
        for ($i = $months * 2 - 1; $i >= $months; $i--) {
            $key = $now->copy()->subMonths($i)->format('Y-m');
            $inc = $monthlyMap[$key]['income'] ?? 0.0;
            $exp = $monthlyMap[$key]['expense'] ?? 0.0;
            $sum += ($inc - $exp);
        }
        return $sum;
    }

    private static function finalizeSeries(array $labels, array $income, array $expense, array $net, float $currentNet, float $previousNet): array
    {
        $maxExpenseIndex = 0;
        $maxExpenseValue = 0.0;
        foreach ($expense as $index => $value) {
            if ($value > $maxExpenseValue) {
                $maxExpenseValue = $value;
                $maxExpenseIndex = $index;
            }
        }

        $changePct = 0.0;
        if ($previousNet != 0.0) {
            $changePct = (($currentNet - $previousNet) / abs($previousNet)) * 100;
        } elseif ($currentNet != 0.0) {
            $changePct = 100.0;
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'expense' => $expense,
            'net' => $net,
            'total_income' => round(array_sum($income), 2),
            'total_expense' => round(array_sum($expense), 2),
            'total_net' => round($currentNet, 2),
            'change_pct' => round($changePct, 2),
            'max_expense_label' => $labels[$maxExpenseIndex] ?? '-',
            'max_expense_value' => round($maxExpenseValue, 2),
        ];
    }
}
