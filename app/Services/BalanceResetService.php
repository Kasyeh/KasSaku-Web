<?php

namespace App\Services;

use App\Models\BalanceModel;
use App\Models\ImpianSetoranModel;
use App\Models\TransactionModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BalanceResetService
{
    public function resetCurrentMonth(int $userId, ?Carbon $now = null): array
    {
        $now = $now ?? Carbon::now();

        return DB::transaction(function () use ($userId, $now) {
            $monthlyTransactions = TransactionModel::where('id_user', $userId)
                ->whereMonth('tanggal', $now->month)
                ->whereYear('tanggal', $now->year);

            $monthlyDreamDeposits = ImpianSetoranModel::where('id_user', $userId)
                ->whereMonth('tanggal', $now->month)
                ->whereYear('tanggal', $now->year);

            $deletedTransactionCount = (clone $monthlyTransactions)->count();
            $deletedDreamDepositCount = (clone $monthlyDreamDeposits)->count();

            if ($deletedTransactionCount > 0) {
                $monthlyTransactions->delete();
            }

            if ($deletedDreamDepositCount > 0) {
                $monthlyDreamDeposits->delete();
            }

            $remainingPemasukan = (float) TransactionModel::where('id_user', $userId)
                ->where('tipe', 'pemasukan')
                ->sum('nominal');

            $remainingPengeluaran = (float) TransactionModel::where('id_user', $userId)
                ->where('tipe', 'pengeluaran')
                ->sum('nominal');

            $remainingSaldo = $remainingPemasukan - $remainingPengeluaran;

            $balance = BalanceModel::firstOrCreate(
                ['id_user' => $userId],
                ['saldo' => 0, 'pemasukan' => 0, 'pengeluaran' => 0]
            );

            $balance->saldo = $remainingSaldo;
            $balance->pemasukan = $remainingPemasukan;
            $balance->pengeluaran = $remainingPengeluaran;
            $balance->save();

            TransactionService::refreshBalance($userId);

            return [
                'deleted_count' => $deletedTransactionCount,
                'deleted_transaction_count' => $deletedTransactionCount,
                'deleted_dream_deposit_count' => $deletedDreamDepositCount,
                'balance' => $balance,
                'saldo' => $remainingSaldo,
                'pemasukan' => $remainingPemasukan,
                'pengeluaran' => $remainingPengeluaran,
            ];
        });
    }
}
