<?php

namespace App\Services;

use App\Models\TransactionModel;
use App\Models\ImpianModel;
use App\Models\ImpianSetoranModel;
use App\Models\BudgetKategoriModel;
use App\Models\BalanceModel;
use Carbon\Carbon;

class NudgeService
{
    /**
     * Generate smart nudges for the given user based on their financial data.
     */
    public static function getSmartNudges(int $userId): array
    {
        $nudges = [];
        $now = Carbon::now();

        // --- 1. Check for over-budget categories ---
        $budgets = BudgetKategoriModel::where('id_user', $userId)->get();
        foreach ($budgets as $budget) {
            $spent = $budget->getSpentAmount();
            $percentage = $budget->getPercentage();

            if ($percentage >= 100) {
                $nudges[] = [
                    'type' => 'over_budget',
                    'icon' => 'warning',
                    'color' => 'rose',
                    'title' => 'Budget ' . ucfirst($budget->kategori) . ' terlampaui',
                    'message' => 'Pengeluaran ' . ucfirst($budget->kategori) . ' sudah ' . round($percentage) . '% dari budget. Pertimbangkan untuk mengurangi pengeluaran di kategori ini.',
                ];
            } elseif ($percentage >= 80) {
                $nudges[] = [
                    'type' => 'budget_warning',
                    'icon' => 'info',
                    'color' => 'amber',
                    'title' => 'Budget ' . ucfirst($budget->kategori) . ' hampir habis',
                    'message' => 'Sudah terpakai ' . round($percentage) . '% dari budget ' . ucfirst($budget->kategori) . '. Sisa budget tinggal sedikit.',
                ];
            }
        }

        // --- 2. Check spending vs income ratio this month ---
        $monthlyIncome = TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pemasukan')
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->sum('nominal');

        $monthlyExpense = TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pengeluaran')
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->sum('nominal');

        if ($monthlyIncome > 0 && $monthlyExpense > $monthlyIncome) {
            $gap = $monthlyExpense - $monthlyIncome;
            $nudges[] = [
                'type' => 'expense_exceeds_income',
                'icon' => 'trending_down',
                'color' => 'rose',
                'title' => 'Pengeluaran melebihi pemasukan',
                'message' => 'Bulan ini pengeluaran lebih tinggi Rp ' . number_format($gap, 0, ',', '.') . ' dari pemasukan. Coba evaluasi kebutuhan vs keinginan.',
            ];
        } elseif ($monthlyIncome > 0 && ($monthlyExpense / $monthlyIncome) > 0.7) {
            $nudges[] = [
                'type' => 'high_spending_ratio',
                'icon' => 'savings',
                'color' => 'amber',
                'title' => 'Rasio pengeluaran tinggi',
                'message' => 'Anda sudah menghabiskan ' . round(($monthlyExpense / $monthlyIncome) * 100) . '% dari pemasukan bulan ini. Sisihkan untuk tabungan.',
            ];
        }

        // --- 3. Check for idle dream savings ---
        $impians = ImpianModel::where('id_user', $userId)->get();
        foreach ($impians as $impian) {
            $lastSetoran = ImpianSetoranModel::where('id_impian', $impian->id_impian)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($lastSetoran) {
                $daysSince = Carbon::parse($lastSetoran->created_at)->diffInDays($now);
                if ($daysSince >= 7) {
                    $nudges[] = [
                        'type' => 'idle_dream',
                        'icon' => 'auto_awesome',
                        'color' => 'violet',
                        'title' => 'Impian "' . $impian->nama . '" menunggu',
                        'message' => 'Sudah ' . $daysSince . ' hari tanpa setoran. Yuk sisihkan sedikit untuk mewujudkan impianmu.',
                    ];
                    break; // Only show one idle dream nudge
                }
            }
        }

        // --- 4. No transactions today ---
        $todayCount = TransactionModel::where('id_user', $userId)
            ->whereDate('tanggal', $now->toDateString())
            ->count();

        if ($todayCount === 0 && $now->hour >= 18) {
            $nudges[] = [
                'type' => 'no_input_today',
                'icon' => 'edit_note',
                'color' => 'sky',
                'title' => 'Belum ada catatan hari ini',
                'message' => 'Jangan lupa catat pengeluaran hari ini. Konsistensi adalah kunci pengelolaan keuangan yang baik.',
            ];
        }

        // --- 5. Positive reinforcement: savings streak ---
        if ($monthlyIncome > 0 && $monthlyExpense <= $monthlyIncome * 0.5) {
            $nudges[] = [
                'type' => 'great_savings',
                'icon' => 'emoji_events',
                'color' => 'emerald',
                'title' => 'Hebat! Pengeluaran terkendali',
                'message' => 'Anda baru memakai ' . round(($monthlyExpense / $monthlyIncome) * 100) . '% dari pemasukan. Pertahankan kebiasaan baik ini.',
            ];
        }

        return array_slice($nudges, 0, 3); // Max 3 nudges
    }
}
