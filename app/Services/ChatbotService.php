<?php

namespace App\Services;

use App\Models\TransactionModel;
use App\Models\BalanceModel;
use Carbon\Carbon;

class ChatbotService
{
    /**
     * Process user message and return a contextual response using local NLP keyword matching.
     */
    public static function processMessage(int $userId, string $message): array
    {
        $messageLower = mb_strtolower(trim($message));
        $now = Carbon::now();

        // Fetch user financial context
        $balance = BalanceModel::where('id_user', $userId)->first();
        $saldo = (float) ($balance->saldo ?? 0);

        $monthlyIncome = (float) TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pemasukan')
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->sum('nominal');

        $monthlyExpense = (float) TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pengeluaran')
            ->whereMonth('tanggal', $now->month)
            ->whereYear('tanggal', $now->year)
            ->sum('nominal');

        $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');

        // --- Keyword Matching ---

        // Saldo / Balance queries
        if (self::matchesAny($messageLower, ['saldo', 'uang saya', 'berapa uang', 'total uang', 'sisa uang'])) {
            return self::reply(
                'Saldo Anda saat ini adalah ' . $fmt($saldo) . '. '
                . 'Bulan ini: pemasukan ' . $fmt($monthlyIncome) . ', pengeluaran ' . $fmt($monthlyExpense) . '.'
            );
        }

        // Expense queries
        if (self::matchesAny($messageLower, ['pengeluaran', 'habis berapa', 'keluar berapa', 'belanja'])) {
            $topCategories = TransactionModel::where('id_user', $userId)
                ->where('tipe', 'pengeluaran')
                ->whereMonth('tanggal', $now->month)
                ->whereYear('tanggal', $now->year)
                ->select('kategori', \DB::raw('SUM(nominal) as total'))
                ->groupBy('kategori')
                ->orderByDesc('total')
                ->limit(3)
                ->get();

            $catList = $topCategories->map(fn($c) => ucfirst($c->kategori) . ' (' . $fmt($c->total) . ')')->implode(', ');

            return self::reply(
                'Pengeluaran bulan ini: ' . $fmt($monthlyExpense) . '. '
                . ($catList ? 'Kategori teratas: ' . $catList . '.' : 'Belum ada data kategori.')
            );
        }

        // Income queries
        if (self::matchesAny($messageLower, ['pemasukan', 'pendapatan', 'gaji', 'masuk berapa'])) {
            return self::reply(
                'Pemasukan bulan ini: ' . $fmt($monthlyIncome) . '. '
                . ($monthlyIncome > $monthlyExpense
                    ? 'Bagus, pemasukan masih lebih besar dari pengeluaran.'
                    : 'Perhatikan, pengeluaran sudah melebihi pemasukan bulan ini.')
            );
        }

        // Tips / advice
        if (self::matchesAny($messageLower, ['tips', 'saran', 'nasihat', 'cara hemat', 'hemat'])) {
            $tips = [
                'Terapkan aturan 50/30/20: 50% kebutuhan, 30% keinginan, 20% tabungan.',
                'Catat setiap pengeluaran, sekecil apa pun. Kesadaran adalah langkah pertama.',
                'Sebelum membeli sesuatu, tunggu 24 jam. Jika masih ingin, baru beli.',
                'Buat anggaran bulanan dan patuhi. Gunakan fitur Budget Kategori di KasSaku.',
                'Sisihkan minimal 10% dari setiap pemasukan untuk dana darurat.',
                'Masak di rumah lebih sering bisa menghemat hingga 40% biaya makan.',
            ];
            return self::reply($tips[array_rand($tips)]);
        }

        // Greeting
        if (self::matchesAny($messageLower, ['halo', 'hai', 'hi', 'hey', 'selamat'])) {
            return self::reply('Halo! Saya asisten keuangan KasSaku. Tanyakan tentang saldo, pengeluaran, pemasukan, atau minta tips keuangan.');
        }

        // Help
        if (self::matchesAny($messageLower, ['bantuan', 'help', 'bisa apa', 'fitur'])) {
            return self::reply(
                'Saya bisa membantu Anda dengan:'
                . "\n- Cek saldo dan ringkasan keuangan"
                . "\n- Analisis pengeluaran dan pemasukan"
                . "\n- Tips dan saran keuangan"
                . "\n- Informasi budget kategori"
                . "\nCoba tanyakan: \"Berapa saldo saya?\" atau \"Tips hemat\""
            );
        }

        // Budget queries
        if (self::matchesAny($messageLower, ['budget', 'anggaran', 'batas'])) {
            $budgets = \App\Models\BudgetKategoriModel::where('id_user', $userId)->get();
            if ($budgets->isEmpty()) {
                return self::reply('Anda belum membuat budget kategori. Buat budget di halaman Statistik untuk mengontrol pengeluaran per kategori.');
            }
            $budgetList = $budgets->map(function ($b) use ($fmt) {
                $spent = $b->getSpentAmount();
                $pct = $b->getPercentage();
                return ucfirst($b->kategori) . ': ' . $fmt($spent) . '/' . $fmt($b->nominal) . ' (' . round($pct) . '%)';
            })->implode("\n");
            return self::reply("Status budget Anda:\n" . $budgetList);
        }

        // Default fallback
        return self::reply(
            'Saya belum sepenuhnya memahami pertanyaan Anda. Coba tanyakan tentang:'
            . "\n- Saldo atau ringkasan keuangan"
            . "\n- Pengeluaran atau pemasukan bulan ini"
            . "\n- Tips keuangan"
            . "\n- Status budget kategori"
        );
    }

    private static function matchesAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private static function reply(string $text): array
    {
        return [
            'success' => true,
            'reply' => $text,
        ];
    }
}
