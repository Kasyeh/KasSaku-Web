<?php

namespace App\Services;

use App\Models\TransactionModel;
use Carbon\Carbon;

class StatisticService
{
    public function buildSixMonthSeries(int $userId, bool $translatedLabels = true): array
    {
        $labels = [];
        $pemasukan = [];
        $pengeluaran = [];
        $net = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);
            $labels[] = $translatedLabels ? $bulan->translatedFormat('M') : $bulan->format('M');

            $inc = TransactionModel::where('id_user', $userId)
                ->where('tipe', 'pemasukan')
                ->whereMonth('tanggal', $bulan->month)
                ->whereYear('tanggal', $bulan->year)
                ->sum('nominal');

            $exp = TransactionModel::where('id_user', $userId)
                ->where('tipe', 'pengeluaran')
                ->whereMonth('tanggal', $bulan->month)
                ->whereYear('tanggal', $bulan->year)
                ->sum('nominal');

            $pemasukan[] = (float) $inc;
            $pengeluaran[] = (float) $exp;
            $net[] = (float) $inc - (float) $exp;
        }

        return [
            'labels' => $labels,
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'net' => $net,
        ];
    }

    public function buildSixMonthInsights(int $userId): array
    {
        $series = $this->buildSixMonthSeries($userId, false);
        $labels = $series['labels'];
        $pengeluaran = $series['pengeluaran'];
        $net = $series['net'];

        $avgSavings = count($net) > 0 ? array_sum($net) / count($net) : 0;

        $activeMonthsWithExp = array_filter($pengeluaran, fn($value) => $value > 0);
        $activeMonthsWithNet = array_filter($net, fn($value) => $value != 0);

        $mostWastefulMonth = null;
        if (count($activeMonthsWithExp) >= 2) {
            $mostWastefulIndex = 0;
            $maxExpense = -1;
            foreach ($pengeluaran as $index => $value) {
                if ($value > $maxExpense) {
                    $maxExpense = $value;
                    $mostWastefulIndex = $index;
                }
            }
            $mostWastefulMonth = $labels[$mostWastefulIndex] ?? null;
        }

        $mostProductiveMonth = null;
        if (count($activeMonthsWithNet) >= 2) {
            $mostProductiveIndex = 0;
            $maxNet = -1000000000;
            foreach ($net as $index => $value) {
                if ($value > $maxNet) {
                    $maxNet = $value;
                    $mostProductiveIndex = $index;
                }
            }
            $mostProductiveMonth = $labels[$mostProductiveIndex] ?? null;
        }

        $trend = 'Stabil';
        if (count($net) >= 2) {
            $lastIndex = count($net) - 1;
            $currentNet = $net[$lastIndex];
            $previousNet = $net[$lastIndex - 1];
            if ($currentNet > $previousNet) {
                $trend = 'Meningkat';
            } elseif ($currentNet < $previousNet) {
                $trend = 'Menurun';
            }
        }

        return [
            'avg_savings' => $avgSavings,
            'most_wasteful_month' => $mostWastefulMonth,
            'most_productive_month' => $mostProductiveMonth,
            'trend' => $trend,
        ];
    }

    public function getMonthTotals(int $userId, Carbon $month): array
    {
        return [
            'pemasukan' => (float) TransactionModel::where('id_user', $userId)
                ->where('tipe', 'pemasukan')
                ->whereMonth('tanggal', $month->month)
                ->whereYear('tanggal', $month->year)
                ->sum('nominal'),
            'pengeluaran' => (float) TransactionModel::where('id_user', $userId)
                ->where('tipe', 'pengeluaran')
                ->whereMonth('tanggal', $month->month)
                ->whereYear('tanggal', $month->year)
                ->sum('nominal'),
        ];
    }
}
