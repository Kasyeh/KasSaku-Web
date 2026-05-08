<?php

namespace App\Services;

use App\Models\BalanceModel;
use App\Models\ImpianModel;
use App\Models\ImpianSetoranModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ImpianProgressService
{
    public static function attachProgressToDream(ImpianModel $dream): ImpianModel
    {
        $danaTerkumpul = (int) ImpianSetoranModel::where('id_impian', $dream->id_impian)->sum('nominal');
        $target = (int) $dream->harga_barang;
        $sisaTarget = max(0, $target - $danaTerkumpul);
        $persentaseProgress = $target > 0 ? min(100, round(($danaTerkumpul / $target) * 100, 2)) : 0;
        $lastSetoran = ImpianSetoranModel::where('id_impian', $dream->id_impian)
            ->orderByDesc('tanggal')
            ->first();

        $dream->setAttribute('dana_terkumpul', $danaTerkumpul);
        $dream->setAttribute('sisa_target', $sisaTarget);
        $dream->setAttribute('persentase_progress', $persentaseProgress);
        $dream->setAttribute('is_tercapai', $sisaTarget <= 0);
        $dream->setAttribute('last_setoran', $lastSetoran ? [
            'tanggal' => $lastSetoran->tanggal?->toDateTimeString(),
            'nominal' => (float) $lastSetoran->nominal,
        ] : null);

        return $dream;
    }

    public static function setoranImpian(
        int $idUser,
        int $idImpian,
        float $nominal,
        ?string $keterangan = null,
        ?string $tanggal = null
    ): array {
        $dream = ImpianModel::where('id_impian', $idImpian)
            ->where('id_user', $idUser)
            ->first();

        if (!$dream) {
            throw ValidationException::withMessages([
                'id_impian' => 'Impian tidak ditemukan atau bukan milik Anda.',
            ]);
        }

        if ($nominal < 1) {
            throw ValidationException::withMessages([
                'nominal' => 'Nominal setoran minimal Rp 1.',
            ]);
        }

        $danaTerkumpul = (int) ImpianSetoranModel::where('id_impian', $idImpian)->sum('nominal');
        $target = (int) $dream->harga_barang;
        $sisaTarget = max(0, $target - $danaTerkumpul);

        if ($sisaTarget <= 0) {
            throw ValidationException::withMessages([
                'nominal' => 'Target impian ini sudah tercapai.',
            ]);
        }

        if ($nominal > $sisaTarget) {
            throw ValidationException::withMessages([
                'nominal' => 'Nominal melebihi sisa target impian.',
            ]);
        }

        $balance = BalanceModel::firstOrCreate(
            ['id_user' => $idUser],
            ['saldo' => 0, 'pemasukan' => 0, 'pengeluaran' => 0]
        );

        if ((float) $balance->saldo < $nominal) {
            throw ValidationException::withMessages([
                'nominal' => 'Saldo Anda tidak cukup untuk melakukan setoran impian.',
            ]);
        }

        $setoran = DB::transaction(function () use ($idUser, $idImpian, $nominal, $keterangan, $tanggal, $dream) {
            $tanggalSetoran = $tanggal ? Carbon::parse($tanggal) : now();

            $setoran = ImpianSetoranModel::create([
                'id_impian' => $idImpian,
                'id_user' => $idUser,
                'nominal' => (int) $nominal,
                'keterangan' => $keterangan,
                'tanggal' => $tanggalSetoran,
            ]);

            $transactionKeterangan = 'Setoran impian: ' . $dream->nama_barang;
            if (!empty($keterangan)) {
                $transactionKeterangan .= ' - ' . $keterangan;
            }

            TransactionService::createTransaction(
                $idUser,
                'pengeluaran',
                (int) $nominal,
                'Tabungan Impian',
                $transactionKeterangan,
                $tanggalSetoran->toDateTimeString()
            );

            return $setoran;
        });

        $dream = self::attachProgressToDream($dream->fresh());

        return [
            'setoran' => $setoran,
            'impian' => $dream,
        ];
    }
}
