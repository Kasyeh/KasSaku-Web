<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BalanceModel;
use App\Models\TransactionModel;
use App\Models\MotivasiModel;
use App\Models\ImpianModel;
use App\Models\BudgetKategoriModel;
use App\Models\NotificationPreference;
use App\Models\NotificationHistory;
use App\Models\PermintaanUnblockModel;
use App\Models\User;
use App\Services\BalanceResetService;
use App\Services\CashflowService;
use App\Services\BalanceService;
use App\Services\FinancialReminderService;
use App\Services\StatisticService;
use App\Services\NudgeService;
use App\Services\ChatbotService;
use App\Services\FirebaseService;
use App\Models\FeedbackModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private function normalizeKategoriOptions(array $items): array
    {
        $normalized = [];
        $seen = [];

        foreach ($items as $item) {
            $value = trim((string) $item);

            if ($value === '') {
                continue;
            }

            $key = mb_strtolower($value);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $normalized[] = $value;
        }

        return $normalized;
    }

    // ===============================
    // DASHBOARD
    // ===============================
    public function homeUser()
    {
        $user = Auth::user();
        $balance = \App\Models\BalanceModel::where('id_user', $user->id_user)->first();
        $nudges = NudgeService::getSmartNudges((int) $user->id_user);
        $motivasi = MotivasiModel::all();

        return view('user.home', [
            'user' => $user,
            'balance' => $balance,
            'nudges' => $nudges,
            'motivasi' => $motivasi
        ]);
    }

    public function accountStatus()
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'active' => (int) ($user->active ?? 0),
            ],
        ]);
    }

    // ===============================
    // STATISTIK / HALAMAN UTAMA
    // ===============================
    public function statUser(Request $request)
    {
        $dashboard = $this->buildStatistikDashboardData((int) Auth::id());

        return view('user.statistik', $dashboard['viewData']);
    }

    public function statistikSnapshot()
    {
        $dashboard = $this->buildStatistikDashboardData((int) Auth::id());

        return response()->json([
            'success' => true,
            'data' => $dashboard['jsonData'],
        ]);
    }

    // ===============================
    // TAMBAH PEMASUKAN
    // ===============================
    public function simpanPemasukan(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1',
            'kategori' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();

        // Use TransactionService to handle saving and RTDB sync
        \App\Services\TransactionService::createTransaction(
            $userId,
            'pemasukan',
            $request->nominal,
            $request->kategori,
            $request->keterangan ?? '-',
            $request->tanggal ?? null
        );

        $dashboard = $this->buildStatistikDashboardData((int) $userId);

        return response()->json([
            'success' => true,
            'message' => 'Pemasukan berhasil ditambahkan',
            'data' => $dashboard['jsonData'],
        ]);
    }

    // ===============================
    // SIMPAN TARGET PENGELUARAN BULANAN
    // ===============================
    public function simpanTargetPengeluaran(Request $request)
    {
        $request->validate([
            'target_pengeluaran' => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();
        $result = app(BalanceService::class)->saveTargetPengeluaran(
            (int) $userId,
            (float) $request->target_pengeluaran
        );
        $balance = $result['balance'];
        $dashboard = $this->buildStatistikDashboardData((int) $userId);

        return response()->json([
            'success' => true,
            'message' => 'Target pengeluaran berhasil disimpan',
            'target_pengeluaran' => $balance->target_pengeluaran,
            'data' => $dashboard['jsonData'],
        ]);
    }

    // ===============================
    // TAMBAH PENGELUARAN
    // ===============================
    public function simpanPengeluaran(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1',
            'kategori' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();

        // Use TransactionService to handle saving and RTDB sync
        \App\Services\TransactionService::createTransaction(
            $userId,
            'pengeluaran',
            $request->nominal,
            $request->kategori,
            $request->keterangan ?? '-',
            $request->tanggal ?? null
        );

        $dashboard = $this->buildStatistikDashboardData((int) $userId);

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil ditambahkan',
            'data' => $dashboard['jsonData'],
        ]);
    }

    private function buildStatistikDashboardData(int $userId): array
    {
        $balanceService = app(BalanceService::class);
        $statisticService = app(StatisticService::class);
        $snapshot = $balanceService->getSnapshot($userId);

        $statistik = $snapshot['balance'] ?? BalanceModel::firstOrCreate(
            ['id_user' => $userId],
            ['saldo' => 0, 'pemasukan' => 0, 'pengeluaran' => 0]
        );

        $transaksi = TransactionModel::where('id_user', $userId)
            ->latest()
            ->take(5)
            ->get();

        $insights = $statisticService->buildSixMonthInsights($userId);
        $avgSavings = (float) ($insights['avg_savings'] ?? 0);
        $mostWastefulMonth = $insights['most_wasteful_month'] ?? null;
        $mostProductiveMonth = $insights['most_productive_month'] ?? null;
        $trend = $insights['trend'] ?? 'Stabil';

        $motivasi = MotivasiModel::latest()->get();
        $impianList = ImpianModel::where('id_user', $userId)
            ->orderBy('deadline', 'asc')
            ->take(3)
            ->get();

        $currentMonthTotals = $statisticService->getMonthTotals($userId, Carbon::now());
        $previousMonthTotals = $statisticService->getMonthTotals($userId, Carbon::now()->copy()->subMonth());

        $monthlyPemasukan = (float) ($snapshot['pemasukan_bulan_ini'] ?? $currentMonthTotals['pemasukan']);
        $monthlyPengeluaran = (float) ($snapshot['pengeluaran_bulan_ini'] ?? $currentMonthTotals['pengeluaran']);
        $prevMonthPemasukan = (float) ($previousMonthTotals['pemasukan'] ?? 0);
        $prevMonthPengeluaran = (float) ($previousMonthTotals['pengeluaran'] ?? 0);

        $statistik->pemasukan = $monthlyPemasukan;
        $statistik->pengeluaran = $monthlyPengeluaran;
        $statistik->saldo = (float) ($snapshot['real_saldo'] ?? ($statistik->saldo ?? 0));

        $targetPengeluaran = array_key_exists('target_pengeluaran', (array) $snapshot)
            ? $snapshot['target_pengeluaran']
            : $statistik->target_pengeluaran;
        $targetPengeluaran = $targetPengeluaran !== null ? (float) $targetPengeluaran : null;

        $isOverBudget = (bool) ($snapshot['is_over_budget'] ?? ($targetPengeluaran !== null && $monthlyPengeluaran > $targetPengeluaran));
        $isExpenseHigherThanIncome = $monthlyPengeluaran > $monthlyPemasukan;
        $expenseIncomeGap = $monthlyPengeluaran - $monthlyPemasukan;
        $targetProgressPercent = $targetPengeluaran !== null
            ? ($targetPengeluaran > 0 ? round(min(100, ($monthlyPengeluaran / $targetPengeluaran) * 100), 2) : 100.0)
            : null;

        $budgetKategori = BudgetKategoriModel::where('id_user', $userId)->get()->map(function ($budget) {
            $budget->spent = $budget->getSpentAmount();
            $budget->percentage = $budget->getPercentage();
            $budget->over = $budget->isOverBudget();
            $budget->periode_label = $budget->getPeriodeLabel();

            return $budget;
        });

        $kategoriList = TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pengeluaran')
            ->select('kategori')
            ->distinct()
            ->pluck('kategori')
            ->filter()
            ->values()
            ->toArray();
        $kategoriList = $this->normalizeKategoriOptions($kategoriList);

        $kategoriCepatPemasukan = TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pemasukan')
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->select('kategori', DB::raw('COUNT(*) as total'))
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->limit(6)
            ->pluck('kategori')
            ->values()
            ->toArray();
        $kategoriCepatPemasukan = $this->normalizeKategoriOptions($kategoriCepatPemasukan);

        $kategoriCepatPengeluaran = TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pengeluaran')
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->select('kategori', DB::raw('COUNT(*) as total'))
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->limit(6)
            ->pluck('kategori')
            ->values()
            ->toArray();
        $kategoriCepatPengeluaran = $this->normalizeKategoriOptions($kategoriCepatPengeluaran);

        $budgetKategoriList = $budgetKategori->pluck('kategori')->filter()->values()->toArray();
        $budgetKategoriList = $this->normalizeKategoriOptions($budgetKategoriList);
        $cashflowSeries = CashflowService::buildSeries($userId);

        $summary = [
            'saldo' => (float) ($statistik->saldo ?? 0),
            'monthly_pemasukan' => $monthlyPemasukan,
            'monthly_pengeluaran' => $monthlyPengeluaran,
            'target_pengeluaran' => $targetPengeluaran,
            'is_over_budget' => $isOverBudget,
            'is_expense_higher_than_income' => $isExpenseHigherThanIncome,
            'expense_income_gap' => $expenseIncomeGap,
            'target_progress_percent' => $targetProgressPercent,
            'prev_month_pemasukan' => $prevMonthPemasukan,
            'prev_month_pengeluaran' => $prevMonthPengeluaran,
            'avg_savings' => $avgSavings,
            'trend' => $trend,
            'trend_icon' => $trend === 'Meningkat' ? 'trending_up' : ($trend === 'Menurun' ? 'trending_down' : 'trending_flat'),
            'most_productive_month' => $mostProductiveMonth,
            'most_wasteful_month' => $mostWastefulMonth,
        ];

        $viewData = [
            'statistik' => $statistik,
            'transaksi' => $transaksi,
            'motivasi' => $motivasi,
            'impianList' => $impianList,
            'monthlyPemasukan' => $monthlyPemasukan,
            'monthlyPengeluaran' => $monthlyPengeluaran,
            'prevMonthPemasukan' => $prevMonthPemasukan,
            'prevMonthPengeluaran' => $prevMonthPengeluaran,
            'targetPengeluaran' => $targetPengeluaran,
            'isOverBudget' => $isOverBudget,
            'isExpenseHigherThanIncome' => $isExpenseHigherThanIncome,
            'expenseIncomeGap' => $expenseIncomeGap,
            'avgSavings' => $avgSavings,
            'mostWastefulMonth' => $mostWastefulMonth,
            'mostProductiveMonth' => $mostProductiveMonth,
            'trend' => $trend,
            'budgetKategori' => $budgetKategori,
            'budgetKategoriList' => $budgetKategoriList,
            'kategoriList' => $kategoriList,
            'kategoriCepatPemasukan' => $kategoriCepatPemasukan,
            'kategoriCepatPengeluaran' => $kategoriCepatPengeluaran,
            'cashflowSeries' => $cashflowSeries,
        ];

        $fragments = $this->renderStatistikFragments($viewData);

        $jsonData = [
            'summary' => $summary,
            'cashflow_series' => $cashflowSeries,
            'fragments' => $fragments,
            'kategori_list' => $kategoriList,
            'kategori_cepat_pemasukan' => $kategoriCepatPemasukan,
            'kategori_cepat_pengeluaran' => $kategoriCepatPengeluaran,
            'budget_kategori_list' => $budgetKategoriList,
        ];

        return [
            'viewData' => $viewData,
            'jsonData' => $jsonData,
        ];
    }

    private function renderStatistikFragments(array $viewData): array
    {
        return [
            'budget_section_html' => view('user.partials.statistik.budget-section', $viewData)->render(),
            'dream_forecast_html' => view('user.partials.statistik.dream-forecast', $viewData)->render(),
            'recent_activities_html' => view('user.partials.statistik.recent-activities', $viewData)->render(),
            'performance_summary_html' => view('user.partials.statistik.performance-summary', $viewData)->render(),
        ];
    }

    // ===============================
    // EXPORT PDF LAPORAN RIWAYAT
    // ===============================
    public function exportPdf(Request $request)
    {
        $userId = Auth::id();

        $query = TransactionModel::where('id_user', $userId);

        // Quick period filters
        if ($request->periode == 'hari_ini') {
            $query->whereDate('tanggal', Carbon::today());
            $tanggal = 'Hari Ini';
        } elseif ($request->periode == 'minggu_ini') {
            $query->whereBetween('tanggal', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            $tanggal = 'Minggu Ini';
        } elseif ($request->periode == 'bulan_ini') {
            $query->whereMonth('tanggal', Carbon::now()->month)
                ->whereYear('tanggal', Carbon::now()->year);
            $tanggal = 'Bulan Ini (' . Carbon::now()->translatedFormat('F Y') . ')';
        } else {
            $tanggal = 'Semua Periode';
        }

        // Manual filters
        if ($request->tanggal) {
            $query->whereDate('tanggal', $request->tanggal);
            $tanggal = Carbon::parse($request->tanggal)->format('d-m-Y');
        }
        if ($request->bulan) {
            $query->whereMonth('tanggal', $request->bulan);
            // if tahun also provided, apply and format
            if ($request->tahun) {
                $query->whereYear('tanggal', $request->tahun);
                $tanggal = Carbon::create()->month($request->bulan)->year($request->tahun)->translatedFormat('F Y');
            } else {
                $tanggal = Carbon::create()->month($request->bulan)->translatedFormat('F');
            }
        }
        if ($request->tahun && !$request->bulan) {
            $query->whereYear('tanggal', $request->tahun);
            $tanggal = $request->tahun;
        }

        if ($request->tipe) {
            $query->where('tipe', $request->tipe);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('kategori', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('keterangan', 'LIKE', '%' . $request->search . '%');
            });
        }

        $transaksi = $query->orderBy('created_at', 'desc')->get();

        if ($transaksi->isEmpty()) {
            return redirect()->back()->with('error', 'Riwayat masih kosong nih');
        }

        $totalPemasukan = $transaksi->where('tipe', 'pemasukan')->sum('nominal');
        $totalPengeluaran = $transaksi->where('tipe', 'pengeluaran')->sum('nominal');
        $saldo = $totalPemasukan - $totalPengeluaran;

        $pdf = Pdf::loadView('user.reportRiwayat', [
            'transaksi' => $transaksi,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldo' => $saldo,
            'tanggal' => $tanggal,
        ]);

        $filename = 'laporan_riwayat_' . date('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    // ===============================
    // HALAMAN PROFIL PENGGUNA
    // ===============================
    // ===============================
    // HALAMAN PROFIL PENGGUNA
    // ===============================
    public function profile()
    {
        $userId = Auth::id();
        $user = Auth::user();
        $notificationPreference = app(FinancialReminderService::class)->resolvePreference($user);

        // Current real-time balance
        $balance = BalanceModel::firstOrCreate(
            ['id_user' => $userId],
            ['saldo' => 0, 'pemasukan' => 0, 'pengeluaran' => 0]
        );

        // This month's totals
        $pemasukanBulan = TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pemasukan')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('nominal');

        $pengeluaranBulan = TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pengeluaran')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('nominal');

        // New Logic: Forward Calculation with Robust Date Matching (YYYY-MM)
        // 1. Get ALL transactions chronological
        $allTransactions = TransactionModel::where('id_user', $userId)
            ->orderBy('tanggal', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Define the 6-month window using strict Y-m keys
        $profileLabels = [];
        $profilePemasukan = [];
        $profilePengeluaran = [];
        $monthlySummary = [];

        // We want last 6 months including current
        $monthsToDisplay = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthsToDisplay[] = [
                'key' => $date->format('Y-m'), // strict key
                'label' => $date->format('M Y') // display label
            ];
        }

        // 3. Replay history
        $tempSummary = []; // Key: 'Y-m' => [inc, exp]

        // Initialize tempSummary
        foreach ($monthsToDisplay as $m) {
            $tempSummary[$m['key']] = [
                'pemasukan' => 0,
                'pengeluaran' => 0,
            ];
        }

        // Calculate Opening Balance (before the 6-month window)
        // Start of window is the 1st day of the 5th month ago
        $startOfWindowCode = Carbon::now()->subMonths(5)->format('Y-m');

        $openingBalance = 0;

        // Iterate through all transactions
        foreach ($allTransactions as $trx) {
            $trxKey = \Carbon\Carbon::parse($trx->tanggal)->format('Y-m');

            // If transaction is OLDER than our window, add to opening balance
            if ($trxKey < $startOfWindowCode) {
                if ($trx->tipe == 'pemasukan')
                    $openingBalance += $trx->nominal;
                else
                    $openingBalance -= $trx->nominal;
            }
            // If transaction is INSIDE our window (or newer), record stats
            elseif (isset($tempSummary[$trxKey])) {
                if ($trx->tipe == 'pemasukan') {
                    $tempSummary[$trxKey]['pemasukan'] += $trx->nominal;
                } else {
                    $tempSummary[$trxKey]['pengeluaran'] += $trx->nominal;
                }
            }
        }

        // 4. Build the Final Summary Array sequentially
        $currentTrackingBalance = $openingBalance;

        foreach ($monthsToDisplay as $m) {
            $key = $m['key'];
            $label = $m['label'];

            $inc = $tempSummary[$key]['pemasukan'];
            $exp = $tempSummary[$key]['pengeluaran'];

            // Calculate Ending Balance for this month
            $currentTrackingBalance = $currentTrackingBalance + $inc - $exp;

            $monthlySummary[] = [
                'label' => $label,
                'pemasukan' => $inc,
                'pengeluaran' => $exp,
                'saldo' => $currentTrackingBalance
            ];

            $profileLabels[] = $label;
            $profilePemasukan[] = $inc;
            $profilePengeluaran[] = $exp;
        }

        // Reverse to chronological order (oldest to newest) - wait, blade loop expects newest first?
        // Usually user wants newest month (Top) to oldest (Bottom).
        $monthlySummaryTable = array_reverse($monthlySummary);

        // For Chart: usually Oldest -> Newest (Left to Right).
        // So $profileLabels should remain Oldest -> Newest.

        // compute average monthly balance (simple average of saldo values if available)
        $saldoPerBulan = 0;
        if (count($monthlySummary)) {
            $totalSaldo = array_sum(array_column($monthlySummary, 'saldo'));
            $saldoPerBulan = round($totalSaldo / count($monthlySummary));
        }

        return view('user.profile', [
            'saldoNow' => $balance->saldo ?? 0,
            'saldoPerBulan' => $saldoPerBulan,
            'pemasukanBulan' => $pemasukanBulan,
            'pengeluaranBulan' => $pengeluaranBulan,
            'monthlySummary' => $monthlySummaryTable, // Reversed for Table
            'profileLabels' => $profileLabels,         // Chronological for Chart
            'profilePemasukan' => $profilePemasukan,
            'profilePengeluaran' => $profilePengeluaran,
            'notificationPreference' => $notificationPreference,
        ]);
    }

    public function updateEmail(Request $request)
    {
        $request->merge([
            'email' => $request->has('email') ? trim((string) $request->email) : '',
        ]);

        $request->validate([
            'email' => 'required|email|unique:users,email,' . Auth::id() . ',id_user',
            'password' => 'required|string',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh pengguna lain. Gunakan alamat email lain.',
            'password.required' => 'Password wajib diisi untuk mengubah email.',
        ]);

        $user = User::find(Auth::id());
        if (!$user || !Hash::check((string) $request->password, (string) $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password salah.',
                    'errors' => [
                        'password' => ['Password salah.'],
                    ],
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors(['password' => 'Password salah.'])
                ->withInput();
        }

        $user->email = $request->email;
        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email berhasil diperbarui',
                'data' => [
                    'email' => $user->email
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Email berhasil diperbarui');
    }

    public function simpanReminderPreference(Request $request)
    {
        $validated = $request->validate([
            'reminders_enabled' => 'nullable|boolean',
            'daily_reminder_enabled' => 'nullable|boolean',
            'daily_reminder_hour' => 'required|integer|min:0|max:23',
            'budget_alert_enabled' => 'nullable|boolean',
            'budget_alert_threshold' => 'required|integer|min:50|max:100',
            'dream_reminder_enabled' => 'nullable|boolean',
            'dream_inactive_days' => 'required|integer|min:1|max:30',
        ]);

        $preference = NotificationPreference::updateOrCreate(
            ['id_user' => Auth::id()],
            [
                'reminders_enabled' => $request->boolean('reminders_enabled'),
                'daily_reminder_enabled' => $request->boolean('daily_reminder_enabled'),
                'daily_reminder_hour' => (int) $validated['daily_reminder_hour'],
                'budget_alert_enabled' => $request->boolean('budget_alert_enabled'),
                'budget_alert_threshold' => (int) $validated['budget_alert_threshold'],
                'dream_reminder_enabled' => $request->boolean('dream_reminder_enabled'),
                'dream_inactive_days' => (int) $validated['dream_inactive_days'],
            ]
        );

        return redirect()
            ->route('profileUser')
            ->with('success', 'Preferensi reminder berhasil diperbarui.');
    }

    public function getNotificationHistory()
    {
        $items = NotificationHistory::where('id_user', Auth::id())
            ->orderByDesc('sent_at')
            ->limit(30)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => (int) $item->id,
                    'category' => $item->category,
                    'title' => $item->title,
                    'body' => $item->body,
                    'sent_at' => $item->sent_at ? $item->sent_at->toIso8601String() : null,
                    'sent_at_human' => $item->sent_at ? $item->sent_at->diffForHumans() : '',
                    'read' => $item->read_at !== null,
                    'accent' => $this->resolveNotificationAccent((string) $item->category),
                    'icon' => $this->resolveNotificationIcon((string) $item->category),
                    'excerpt' => Str::limit((string) $item->body, 110),
                ];
            })
            ->values();

        $unreadCount = NotificationHistory::where('id_user', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'unread_count' => $unreadCount,
            ],
        ]);
    }

    public function markNotificationHistoryAsRead()
    {
        NotificationHistory::where('id_user', Auth::id())
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai sudah dibaca.',
        ]);
    }

    private function resolveNotificationAccent(string $category): string
    {
        switch ($category) {
            case 'transaction':
                return 'emerald';
            case 'admin':
            case 'account_blocked':
            case 'account_unblocked':
            case 'unblock_approved':
                return 'amber';
            case 'budget_alert':
            case 'budget_exceeded':
                return 'rose';
            case 'daily_input':
                return 'sky';
            case 'dream_progress':
            case 'dream_reminder':
            case 'dream_reached':
                return 'violet';
            default:
                return 'slate';
        }
    }

    private function resolveNotificationIcon(string $category): string
    {
        switch ($category) {
            case 'transaction':
                return 'payments';
            case 'admin':
            case 'account_blocked':
            case 'account_unblocked':
            case 'unblock_approved':
                return 'campaign';
            case 'budget_alert':
            case 'budget_exceeded':
                return 'warning';
            case 'daily_input':
                return 'edit_note';
            case 'dream_progress':
            case 'dream_reminder':
            case 'dream_reached':
                return 'auto_awesome';
            default:
                return 'notifications';
        }
    }

    /**
     * Reset saldo user (hanya bulan ini) dan hapus riwayat transaksi bulan ini (Web)
     */
    public function resetSaldo(Request $request)
    {
        $request->validate([
            'password' => 'required'
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return redirect()->back()->with('error', 'Password yang Anda masukkan salah.');
        }

        $userId = Auth::id();

        try {
            app(BalanceResetService::class)->resetCurrentMonth((int) $userId);

            return redirect()->back()->with('success', 'Saldo dan riwayat transaksi bulan ini berhasil direset');
        } catch (\Throwable $e) {
            \Log::error('Web reset saldo failed', [
                'id_user' => $userId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Gagal meriset saldo. Silakan coba lagi.');
        }
    }

    // ===============================
    // BUDGET PER KATEGORI
    // ===============================
    public function simpanBudgetKategori(Request $request)
    {
        $request->validate([
            'kategori' => 'required|string|max:100',
            'nominal' => 'required|numeric|min:1',
            'periode' => 'required|in:mingguan,bulanan,custom',
            'tanggal_mulai' => 'nullable|required_if:periode,custom|date',
            'tanggal_akhir' => 'nullable|required_if:periode,custom|date|after_or_equal:tanggal_mulai',
        ]);

        $userId = Auth::id();

        // Update or create budget for this kategori + periode combo
        $budget = BudgetKategoriModel::updateOrCreate(
            [
                'id_user' => $userId,
                'kategori' => strtolower(trim($request->kategori)),
                'periode' => $request->periode,
                'tanggal_mulai' => $request->periode === 'custom' ? $request->tanggal_mulai : null,
            ],
            [
                'nominal' => $request->nominal,
                'tanggal_akhir' => $request->periode === 'custom' ? $request->tanggal_akhir : null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Budget kategori "' . ucfirst($budget->kategori) . '" berhasil disimpan',
        ]);
    }

    public function hapusBudgetKategori($id)
    {
        $budget = BudgetKategoriModel::where('id', $id)
            ->where('id_user', Auth::id())
            ->firstOrFail();

        $nama = ucfirst($budget->kategori);
        $budget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Budget kategori "' . $nama . '" berhasil dihapus',
        ]);
    }

    public function mintaUnblock(Request $request)
    {
        $request->validate([
            'id_user' => 'required',
            'pesan' => 'required'
        ]);

        $existing = PermintaanUnblockModel::where('id_user', $request->id_user)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah memiliki permintaan yang sedang diproses. Mohon tunggu tinjauan admin.');
        }

        PermintaanUnblockModel::create([
            'id_user' => $request->id_user,
            'pesan' => $request->pesan,
            'status' => 'pending'
        ]);

        // Notify Admin via RTDB
        try {
            $user = \App\Models\User::where('id_user', $request->id_user)->first();
            $firebaseService = app(\App\Services\FirebaseService::class);
            $firebaseService->notifyNewUnblockRequest([
                'id_user' => $user->id_user,
                'username' => $user->username,
                'pesan' => $request->pesan
            ]);
        } catch (\Exception $e) {
            \Log::error('RTDB Notify Error: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Permintaan unblock telah dikirim ke admin.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = User::find(Auth::id());
        if (!$user || !Hash::check($request->current_password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password saat ini salah.',
                ], 422);
            }
            return redirect()->back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah.',
            ]);
        }
        return redirect()->back()->with('success', 'Password berhasil diubah.');
    }

    public function updateCurrency(Request $request)
    {
        $request->validate([
            'currency' => 'required|string|in:IDR,USD,MYR',
            'currency_format' => 'nullable|string|in:compact,standard',
        ]);

        $user = User::find(Auth::id());
        $user->currency = $request->currency;
        if ($request->has('currency_format')) {
            $user->currency_format = $request->currency_format;
        }
        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pengaturan mata uang berhasil diperbarui.',
                'data' => [
                    'currency' => $user->currency,
                    'currency_format' => $user->currency_format,
                ]
            ]);
        }
        return redirect()->back()->with('success', 'Pengaturan mata uang berhasil diperbarui.');
    }

    public function sendFeedbackWeb(Request $request)
    {
        $request->validate([
            'subjek' => 'required|string|max:255',
            'pesan' => 'required|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $user = Auth::user();
        $feedback = FeedbackModel::create([
            'id_user' => $user->id_user,
            'subjek' => $request->subjek,
            'pesan' => $request->pesan,
            'rating' => $request->rating,
            'status' => 'pending', // matching feedback table columns
        ]);

        try {
            $firebaseService = app(FirebaseService::class);
            $firebaseService->notifyNewFeedback([
                'id_user' => $user->id_user,
                'username' => $user->username,
                'subjek' => $feedback->subjek
            ]);
        } catch (\Exception $e) {
            \Log::error('RTDB Feedback Notify Error: ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Umpan balik berhasil dikirim!',
            ]);
        }
        return redirect()->back()->with('success', 'Umpan balik berhasil dikirim!');
    }

    public function webChatbotAsk(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userId = Auth::id();
        $result = ChatbotService::processMessage((int) $userId, $request->message);

        return response()->json($result);
    }

    public function webChatbotReset(Request $request)
    {
        $userId = Auth::id();
        $cacheKey = 'chatbot_history_' . $userId;
        cache()->forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat obrolan asisten berhasil direset.',
        ]);
    }
}
