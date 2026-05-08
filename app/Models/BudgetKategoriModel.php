<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BudgetKategoriModel extends Model
{
    use HasFactory;

    protected $table = 'tb_budget_kategori';

    protected $fillable = [
        'id_user',
        'kategori',
        'nominal',
        'periode',
        'tanggal_mulai',
        'tanggal_akhir',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'tanggal_mulai' => 'date',
        'tanggal_akhir' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Hitung total nominal transaksi kategori ini berdasarkan periode.
     * Hanya menghitung pengeluaran karena budget kategori berlaku untuk expense.
     */
    public function getSpentAmount(): float
    {
        [$effectiveStart, $periodEnd] = $this->resolveUsageWindow();

        $query = TransactionModel::where('id_user', $this->id_user)
            ->whereRaw('LOWER(TRIM(tipe)) = ?', ['pengeluaran'])
            ->whereRaw('LOWER(TRIM(kategori)) = ?', [strtolower(trim((string) $this->kategori))])
            ->whereBetween('tanggal', [
                $effectiveStart->toDateTimeString(),
                $periodEnd->toDateTimeString(),
            ]);

        return (float) $query->sum('nominal');
    }

    private function resolveUsageWindow(): array
    {
        $now = Carbon::now();

        switch ($this->periode) {
            case 'mingguan':
                $periodStart = $now->copy()->startOfWeek();
                $periodEnd = $now->copy()->endOfWeek();
                break;
            case 'bulanan':
                $periodStart = $now->copy()->startOfMonth();
                $periodEnd = $now->copy()->endOfMonth();
                break;
            case 'custom':
                if ($this->tanggal_mulai && $this->tanggal_akhir) {
                    $periodStart = Carbon::parse($this->tanggal_mulai)->startOfDay();
                    $periodEnd = Carbon::parse($this->tanggal_akhir)->endOfDay();
                } else {
                    $periodStart = ($this->created_at ? Carbon::parse($this->created_at) : $now->copy())->startOfDay();
                    $periodEnd = $now->copy()->endOfDay();
                }
                break;
            default:
                $periodStart = $now->copy()->startOfMonth();
                $periodEnd = $now->copy()->endOfMonth();
                break;
        }

        $createdAt = $this->created_at ? Carbon::parse($this->created_at) : $periodStart->copy();
        $effectiveStart = $createdAt->greaterThan($periodStart) ? $createdAt : $periodStart;

        if ($effectiveStart->greaterThan($periodEnd)) {
            $periodEnd = $effectiveStart->copy();
        }

        return [$effectiveStart, $periodEnd];
    }

    /**
     * Cek apakah budget sudah melebihi limit
     */
    public function isOverBudget(): bool
    {
        return $this->getSpentAmount() > $this->nominal;
    }

    /**
     * Hitung persentase terpakai
     */
    public function getPercentage(): float
    {
        if ($this->nominal <= 0)
            return 0;
        return min(100, ($this->getSpentAmount() / $this->nominal) * 100);
    }

    /**
     * Label periode untuk display
     */
    public function getPeriodeLabel(): string
    {
        switch ($this->periode) {
            case 'mingguan':
                return 'Minggu ini';
            case 'bulanan':
                return Carbon::now()->translatedFormat('F Y');
            case 'custom':
                if ($this->tanggal_mulai && $this->tanggal_akhir) {
                    return Carbon::parse($this->tanggal_mulai)->format('d M') . ' - ' . Carbon::parse($this->tanggal_akhir)->format('d M Y');
                }
                return 'Custom';
            default:
                return '';
        }
    }
}
