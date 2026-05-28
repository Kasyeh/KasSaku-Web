<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BalanceModel; // Model untuk data saldo
use App\Models\MotivasiModel;
use App\Models\User;         // Model User untuk mendapatkan username
use App\Models\TransactionModel;
use App\Models\ImpianModel;
use App\Models\BudgetKategoriModel;
use App\Models\PermintaanUnblockModel;
use App\Models\FeedbackModel;
use Illuminate\Support\Facades\Hash;
use App\Services\TransactionService;
use App\Services\CashflowService;
use App\Services\ImpianProgressService;
use App\Services\BalanceService;
use App\Services\BalanceResetService;
use App\Services\StatisticService;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OTPMail;

class ApiController extends Controller
{
    public function getSaldo(Request $request, $id_user = null)
    {
        if ($response = $this->guardPathUserIdMatch($request, $id_user)) {
            return $response;
        }
        $id_user = $request->user()->id_user;

        $snapshot = app(BalanceService::class)->getSnapshot((int) $id_user);

        if (!$snapshot) {
            return response()->json([
                'success' => false,
                'message' => 'Data saldo tidak ditemukan',
                'data' => null
            ], 404);
        }

        $balance = $snapshot['balance'];
        $user = $snapshot['user'];

        return response()->json([
            'success' => true,
            'message' => ($snapshot['real_saldo'] == 0) ? 'Saldo masih kosong' : 'Data saldo berhasil diambil',
            'data' => [
                'id_user' => (int) $balance->id_user,
                'username' => $user ? $user->username : 'Tidak diketahui',
                'email' => $user ? $user->email : null,
                'avatar' => $user ? $user->avatar : null,
                'saldo' => (string) $snapshot['real_saldo'],
                'pemasukan' => (string) $snapshot['pemasukan_bulan_ini'],
                'pengeluaran' => (string) $snapshot['pengeluaran_bulan_ini'],
                'target_pengeluaran' => $snapshot['target_pengeluaran'] ? (string) $snapshot['target_pengeluaran'] : null,
                'is_over_budget' => $snapshot['is_over_budget']
            ]
        ]);
    }

    /**
     * Simpan target pengeluaran bulanan
     */
    public function simpanTargetPengeluaran(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'nullable|numeric',
            'target_pengeluaran' => 'required|numeric|min:0',
        ]);

        if ($response = $this->guardBodyUserIdMatch($request, $validated)) {
            return $response;
        }
        $validated['id_user'] = $request->user()->id_user;
        $result = app(BalanceService::class)->saveTargetPengeluaran(
            (int) $validated['id_user'],
            (float) $validated['target_pengeluaran']
        );
        $balance = $result['balance'];

        return response()->json([
            'success' => true,
            'message' => 'Target pengeluaran berhasil disimpan',
            'data' => [
                'target_pengeluaran' => $balance->target_pengeluaran ? (string) $balance->target_pengeluaran : null,
                'pengeluaran_bulan_ini' => (string) $result['pengeluaran_bulan_ini'],
                'is_over_budget' => $result['is_over_budget']
            ]
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->role === 'admin') {
                Auth::logout();
                return response()->json([
                    'response_code' => 403,
                    'message' => 'Admin tidak diperbolehkan login ke aplikasi Android.',
                    'content' => null
                ], 403);
            }

            if ($user->active == 0) {
                $userId = $user->id_user;
                Auth::logout();

                // Check for unblock request
                $latestRequest = \App\Models\PermintaanUnblockModel::where('id_user', $userId)
                    ->orderBy('updated_at', 'desc')
                    ->first();

                return response()->json([
                    'response_code' => 403,
                    'message' => 'Akun Anda sedang diblokir.',
                    'content' => [
                        'id_user' => $userId,
                        'pending_unblock' => ($latestRequest && $latestRequest->status == 'pending'),
                        'rejected_unblock' => ($latestRequest && $latestRequest->status == 'ditolak'),
                        'rejected_message' => ($latestRequest && $latestRequest->status == 'ditolak')
                            ? ($latestRequest->alasan_admin ? 'Permintaan ditolak. Alasan: ' . $latestRequest->alasan_admin : 'Permintaan unblock ditolak oleh Admin.')
                            : null
                    ]
                ], 403);
            }

            // PASTIKAN $user->id_user MEMILIKI NILAI YANG VALID DI DATABASE ANDA (TIDAK NULL)
            if ($user && $user->id_user) { // Tambahkan pengecekan untuk memastikan $user->id_user tidak null
                // Revoke previous tokens dan buat token baru
                $user->tokens()->delete();
                $token = $user->createToken('android-token')->plainTextToken;

                if ($request->filled('fcm_token')) {
                    $user->fcm_token = $request->input('fcm_token');
                    $user->save();
                }

                // Sync active status to Firebase RTDB so realtime listener has correct baseline
                try {
                    $firebaseService = app(\App\Services\FirebaseService::class);
                    $firebaseService->updateUserStatus($user->id_user, 1);
                    $firebaseService->notifyUserAccountEvent(
                        $user->id_user,
                        'login',
                        'Sesi aktif.',
                        ['active' => 1]
                    );
                } catch (\Exception $e) {
                    \Log::error('RTDB login status sync error: ' . $e->getMessage());
                }

                return response()->json([
                    'response_code' => 200,
                    'message' => 'Login Berhasil',
                    'content' => [
                        'id_user' => $user->id_user,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role,
                        'active' => $user->active,
                        'avatar' => $user->avatar,
                        'token' => $token,
                    ]
                ]);
            } else {
                // Kasus jika login berhasil tetapi id_user null (seharusnya tidak terjadi untuk user valid)
                return response()->json([
                    'response_code' => 500,
                    'message' => 'Login berhasil tetapi data ID pengguna tidak valid.',
                    'content' => null
                ], 500);
            }
        } else {
            return response()->json([
                'response_code' => 401,
                'message' => 'Username atau Password Tidak Ditemukan!',
                'content' => null
            ], 401);
        }
    }

    public function submitUnblockRequest(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|exists:users,username',
            'password' => 'required|string',
            'pesan' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        try {
            $user = User::where('username', $validated['username'])->first();
            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kredensial tidak valid.'
                ], 401);
            }

            if ((int) $user->active === 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak sedang diblokir.'
                ], 400);
            }

            // Cek apakah sudah ada permintaan pending agar tidak duplikat
            $existing = \App\Models\PermintaanUnblockModel::where('id_user', $user->id_user)
                ->where('status', 'pending')
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah memiliki permintaan yang sedang diproses.'
                ], 400);
            }

            \App\Models\PermintaanUnblockModel::create([
                'id_user' => $user->id_user,
                'pesan' => $validated['pesan'],
                'status' => 'pending'
            ]);

            // Save fcm_token to user if provided
            if (!empty($validated['fcm_token'])) {
                $user->fcm_token = $validated['fcm_token'];
                $user->save();
                \Log::info('FCM token updated during unblock request', ['user_id' => $user->id_user]);
            }

            // Notify Admin via RTDB
            try {
                $firebaseService = app(\App\Services\FirebaseService::class);
                $firebaseService->notifyNewUnblockRequest([
                    'id_user' => $user->id_user,
                    'username' => $user->username,
                    'pesan' => $validated['pesan']
                ]);
            } catch (\Exception $e) {
                // Ignore error, log it
                \Log::error('RTDB Notify Error: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Permintaan unblock telah dikirim ke admin.'
            ]);
        } catch (\Throwable $e) {
            \Log::error('Submit unblock request failed', [
                'username' => $request->input('username'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim permintaan unblock. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Registrasi user melalui API
     * Validasi username & password, buat user, inisialisasi saldo, dan kembalikan token
     */
    public function actionRegister(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*_]/',
                'not_regex:/\s/',
            ],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.max' => 'Username maksimal 50 karakter.',
            'username.unique' => 'Username sudah digunakan, coba nama lain.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.regex' => 'Password harus mengandung huruf kapital, angka, simbol, dan tidak mengandung spasi.',
            'password.not_regex' => 'Password tidak boleh mengandung spasi.',
        ]);

        try {
            // Buat user baru
            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'] ?? null,
                'password' => Hash::make($validated['password']),
                'role' => 'user',
                'active' => 1,
            ]);

            // Inisialisasi saldo default jika tabel saldo ada
            if (class_exists(\App\Models\BalanceModel::class)) {
                BalanceModel::create([
                    'id_user' => $user->id_user,
                    'saldo' => 0,
                    'pemasukan' => 0,
                    'pengeluaran' => 0,
                ]);
            }

            // Buat token API (Sanctum)
            $token = null;
            if (method_exists($user, 'createToken')) {
                $token = $user->createToken('default-token')->plainTextToken;
            }

            return response()->json([
                'success' => true,
                'message' => 'Registrasi berhasil',
                'data' => [
                    'id_user' => $user->id_user,
                    'username' => $user->username,
                    'role' => $user->role,
                    'active' => $user->active,
                    'avatar' => $user->avatar,
                    'token' => $token,
                ]
            ], 201);

        } catch (\Throwable $e) {
            \Log::error('Register API failed', [
                'username' => $request->input('username'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kendala saat registrasi. Silakan coba lagi.'
            ], 500);
        }
    }

    public function apiSendOtp(Request $request)
    {
        $request->merge([
            'email' => $request->has('email') ? trim((string) $request->email) : '',
            'username' => $request->has('username') ? trim((string) $request->username) : '',
        ]);

        $validator = \Validator::make($request->all(), [
            'username' => 'required|string|min:2|max:100',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::where('username', $request->username)
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Username dan email tidak cocok dengan akun terdaftar.',
            ], 422);
        }
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_otps')->updateOrInsert(
            ['email' => $request->email],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        try {
            Mail::to($request->email)->send(new OTPMail($otp, $user->username));
            return response()->json([
                'success' => true,
                'message' => 'Kode OTP telah dikirim ke email Anda.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Mail Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function apiResetPassword(Request $request)
    {
        $otpDigits = preg_replace('/\D/', '', (string) $request->input('otp', ''));
        $request->merge([
            'email' => $request->has('email') ? trim((string) $request->email) : '',
            'username' => $request->has('username') ? trim((string) $request->username) : '',
            'otp' => $otpDigits,
        ]);

        $validator = \Validator::make($request->all(), [
            'username' => 'required|string|min:2|max:100',
            'email' => 'required|email',
            'otp' => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'password' => 'required|string|min:8',
        ], [
            'otp.regex' => 'Kode OTP harus 6 digit angka.',
            'otp.size' => 'Kode OTP harus tepat 6 digit.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::where('username', $request->username)
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Username dan email tidak cocok dengan akun terdaftar.',
            ], 422);
        }

        $resetData = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$resetData) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid.'
            ], 400);
        }

        if (Carbon::parse($resetData->expires_at)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP telah kadaluarsa.'
            ], 400);
        }
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_otps')->where('email', $request->email)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah. Silakan login kembali.'
        ]);
    }

    public function getRiwayat(Request $request, $id_user = null)
    {
        if ($response = $this->guardPathUserIdMatch($request, $id_user)) {
            return $response;
        }
        $id_user = $request->user()->id_user;

        $periode = $request->query('periode', 'semua');
        $jenis = $request->query('jenis'); // pemasukan / pengeluaran
        $search = $request->query('search');
        $tanggal = $request->query('tanggal'); // Y-m-d
        $bulan = $request->query('bulan'); // 1-12
        $tahun = $request->query('tahun'); // YYYY

        $transaksiQuery = TransactionModel::where('id_user', $id_user);

        // Filter Jenis
        if ($jenis) {
            $transaksiQuery->where('tipe', $jenis);
        }

        // Filter Periode (jika tidak ada filter spesifik)
        if (!$tanggal && !$bulan && !$tahun) {
            if ($periode === 'hari_ini') {
                $transaksiQuery->whereDate('tanggal', Carbon::today());
            } elseif ($periode === 'minggu_ini') {
                $transaksiQuery->whereBetween('tanggal', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
            } elseif ($periode === 'bulan_ini') {
                $transaksiQuery->whereMonth('tanggal', Carbon::now()->month)
                    ->whereYear('tanggal', Carbon::now()->year);
            }
        }

        // Filter Spesifik (Override periode)
        if ($tanggal) {
            $transaksiQuery->whereDate('tanggal', $tanggal);
        }
        if ($bulan) {
            $transaksiQuery->whereMonth('tanggal', $bulan);
        }
        if ($tahun) {
            $transaksiQuery->whereYear('tanggal', $tahun);
        }

        if ($search) {
            $transaksiQuery->where(function ($q) use ($search) {
                $q->where('kategori', 'LIKE', '%' . $search . '%')
                    ->orWhere('keterangan', 'LIKE', '%' . $search . '%');
            });
        }

        $transaksi = $transaksiQuery->orderBy('created_at', 'desc')->get();

        // Ubah format output agar lebih terstruktur
        $data = $transaksi->map(function ($item) {
            return [
                'id_transaksi' => $item->id_transaksi ?? $item->id,
                'id_user' => $item->id_user,
                'tipe' => $item->tipe,
                'nominal' => (float) $item->nominal,
                'kategori' => $item->kategori,
                'keterangan' => $item->keterangan,
                'tanggal' => $item->tanggal,
                'created_at' => $item->created_at?->toDateTimeString(),
                'updated_at' => $item->updated_at?->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data riwayat transaksi berhasil diambil',
            'dataPage' => [
                'riwayatItems' => $data
            ]
        ]);
    }


    public function tambahPemasukan(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'id_user' => 'nullable|numeric',
            'nominal' => 'required|numeric|min:1',
            'kategori' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'tanggal' => 'nullable|date',
        ]);

        if ($response = $this->guardBodyUserIdMatch($request, $validated)) {
            return $response;
        }
        $id_user = $request->user()->id_user;

        // Use TransactionService to create transaction and update balance
        $transaction = TransactionService::createTransaction(
            $id_user,
            'pemasukan',
            $validated['nominal'],
            $validated['kategori'],
            $validated['keterangan'] ?? null,
            $validated['tanggal'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Pemasukan berhasil ditambahkan!',
            'data' => [
                'id_user' => $id_user,
                'nominal' => $validated['nominal'],
                'kategori' => $validated['kategori'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]
        ]);
    }

    public function tambahPengeluaran(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'id_user' => 'nullable|numeric',
            'nominal' => 'required|numeric|min:1',
            'kategori' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
            'tanggal' => 'nullable|date',
        ]);

        if ($response = $this->guardBodyUserIdMatch($request, $validated)) {
            return $response;
        }
        $id_user = $request->user()->id_user;

        // Use TransactionService to create transaction and update balance
        $transaction = TransactionService::createTransaction(
            $id_user,
            'pengeluaran',
            $validated['nominal'],
            $validated['kategori'],
            $validated['keterangan'] ?? null,
            $validated['tanggal'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengeluaran berhasil ditambahkan!',
            'data' => [
                'id_user' => $id_user,
                'nominal' => $validated['nominal'],
                'kategori' => $validated['kategori'],
                'keterangan' => $validated['keterangan'] ?? null,
            ]
        ]);
    }

    /**
     * Ambil daftar impian untuk user yang sedang login
     * Mengembalikan struktur JSON dengan daftar impian yang dimiliki user
     */
    public function tambahImpian(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'id_user' => 'nullable|numeric',
            'nama_barang' => 'required|string|max:255',
            'foto_barang' => 'required|image|mimes:jpeg,png,jpg|max:2048', // max 2MB
            'harga_barang' => 'required|numeric|min:1',
            'deadline' => 'required|date|after:today',
            'keterangan' => 'nullable|string|max:255',
        ]);

        if ($response = $this->guardBodyUserIdMatch($request, $validated)) {
            return $response;
        }
        $validated['id_user'] = $request->user()->id_user;

        // Upload dan simpan foto
        $fotoPath = null;
        if ($request->hasFile('foto_barang')) {
            $foto = $request->file('foto_barang');
            $fotoPath = $foto->store('impian', 'public');
        }

        // Buat record impian baru
        $impian = ImpianModel::create([
            'id_user' => $validated['id_user'],
            'nama_barang' => $validated['nama_barang'],
            'foto_barang' => $fotoPath,
            'harga_barang' => $validated['harga_barang'],
            'deadline' => $validated['deadline'],
            'keterangan' => !empty($validated['keterangan']) ? $validated['keterangan'] : null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Impian berhasil ditambahkan!',
            'data' => [
                'id_impian' => $impian->id_impian,
                'id_user' => $impian->id_user,
                'nama_barang' => $impian->nama_barang,
                'foto_barang' => $fotoPath,
                'harga_barang' => (float) $impian->harga_barang,
                'deadline' => $impian->deadline,
                'keterangan' => $impian->keterangan,
            ]
        ], 201);
    }

    public function getImpian(Request $request, $id_user = null)
    {
        if ($response = $this->guardPathUserIdMatch($request, $id_user)) {
            return $response;
        }
        $id_user = $request->user()->id_user;

        // Ambil impian berdasarkan id_user
        $impianQuery = ImpianModel::where('id_user', $id_user);

        // Optional: support query params like ?order=deadline_desc
        if ($request->query('order') === 'deadline_desc') {
            $impianQuery->orderBy('deadline', 'desc');
        } else {
            $impianQuery->orderBy('deadline', 'asc');
        }

        $impian = $impianQuery->get()->map(function ($item) {
            return ImpianProgressService::attachProgressToDream($item);
        });

        $data = $impian->map(function ($item) {
            return [
                'id_impian' => $item->id_impian ?? $item->id,
                'id_user' => $item->id_user,
                'nama_barang' => $item->nama_barang,
                'foto_barang' => $item->foto_barang,
                'harga_barang' => (float) $item->harga_barang,
                'deadline' => $item->deadline,
                'keterangan' => $item->keterangan,
                'dana_terkumpul' => (float) ($item->dana_terkumpul ?? 0),
                'sisa_target' => (float) ($item->sisa_target ?? 0),
                'persentase_progress' => (float) ($item->persentase_progress ?? 0),
                'is_tercapai' => (bool) ($item->is_tercapai ?? false),
                'last_setoran' => $item->last_setoran,
                'created_at' => $item->created_at?->toDateTimeString(),
                'updated_at' => $item->updated_at?->toDateTimeString(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data impian berhasil diambil',
            'dataPage' => [
                'impianItems' => $data
            ]
        ]);
    }

    public function setorImpian(Request $request, $id_impian)
    {
        $validated = $request->validate([
            'id_user' => 'nullable|numeric',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:255',
            'tanggal' => 'nullable|date',
        ]);

        if ($response = $this->guardBodyUserIdMatch($request, $validated)) {
            return $response;
        }
        $validated['id_user'] = $request->user()->id_user;

        $result = ImpianProgressService::setoranImpian(
            (int) $validated['id_user'],
            (int) $id_impian,
            (float) $validated['nominal'],
            $validated['keterangan'] ?? null,
            $validated['tanggal'] ?? null
        );

        $setoran = $result['setoran'];
        $impian = $result['impian'];

        return response()->json([
            'success' => true,
            'message' => 'Setoran impian berhasil disimpan.',
            'data' => [
                'setoran' => [
                    'id_setoran_impian' => $setoran->id_setoran_impian,
                    'id_impian' => $setoran->id_impian,
                    'id_user' => $setoran->id_user,
                    'nominal' => (float) $setoran->nominal,
                    'keterangan' => $setoran->keterangan,
                    'tanggal' => $setoran->tanggal?->toDateTimeString(),
                ],
                'impian' => [
                    'id_impian' => $impian->id_impian,
                    'dana_terkumpul' => (float) ($impian->dana_terkumpul ?? 0),
                    'sisa_target' => (float) ($impian->sisa_target ?? 0),
                    'persentase_progress' => (float) ($impian->persentase_progress ?? 0),
                    'is_tercapai' => (bool) ($impian->is_tercapai ?? false),
                    'last_setoran' => $impian->last_setoran,
                ],
            ],
        ]);
    }

    // ===============================
    // EXPORT PDF LAPORAN RIWAYAT (API untuk Android Compose)
    // ===============================
    /**
     * Export laporan riwayat transaksi sebagai PDF
     * 
     * @param Request $request
     * @param int $id_user
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse PDF file stream or JSON error
     * 
     * Query Parameters:
     * - periode: hari_ini, minggu_ini, bulan_ini, semua (default: semua)
     * - tanggal: format Y-m-d (filter tanggal spesifik)
     * - bulan: 1-12 (filter bulan)
     * - tahun: YYYY (filter tahun)
     */
    public function exportPdf(Request $request, $id_user = null)
    {
        if ($response = $this->guardPathUserIdMatch($request, $id_user)) {
            return $response;
        }
        $id_user = $request->user()->id_user;

        // Validasi user exists
        $user = User::find($id_user);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $query = TransactionModel::where('id_user', $id_user);
        $periode = $request->query('periode', 'semua');

        // Quick period filters
        if ($periode == 'hari_ini') {
            $query->whereDate('tanggal', Carbon::today());
            $tanggal = 'Hari Ini';
        } elseif ($periode == 'minggu_ini') {
            $query->whereBetween('tanggal', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            $tanggal = 'Minggu Ini';
        } elseif ($periode == 'bulan_ini') {
            $query->whereMonth('tanggal', Carbon::now()->month)
                ->whereYear('tanggal', Carbon::now()->year);
            $tanggal = 'Bulan Ini (' . Carbon::now()->translatedFormat('F Y') . ')';
        } else {
            $tanggal = 'Semua Periode';
        }

        // Manual filters (override periode jika ada)
        if ($request->query('tanggal')) {
            $query->whereDate('tanggal', $request->query('tanggal'));
            $tanggal = Carbon::parse($request->query('tanggal'))->format('d-m-Y');
        }
        if ($request->query('bulan')) {
            $query->whereMonth('tanggal', $request->query('bulan'));
            if ($request->query('tahun')) {
                $query->whereYear('tanggal', $request->query('tahun'));
                $tanggal = Carbon::create()->month($request->query('bulan'))->year($request->query('tahun'))->translatedFormat('F Y');
            } else {
                $tanggal = Carbon::create()->month($request->query('bulan'))->translatedFormat('F');
            }
        }
        if ($request->query('tahun') && !$request->query('bulan')) {
            $query->whereYear('tanggal', $request->query('tahun'));
            $tanggal = $request->query('tahun');
        }

        if ($request->query('tipe')) {
            $query->where('tipe', $request->query('tipe'));
        }

        if ($request->query('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('kategori', 'LIKE', '%' . $search . '%')
                    ->orWhere('keterangan', 'LIKE', '%' . $search . '%');
            });
        }

        $transaksi = $query->orderBy('created_at', 'desc')->get();

        if ($transaksi->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Riwayat masih kosong nih'
            ], 422);
        }

        // Hitung total
        $totalPemasukan = $transaksi->where('tipe', 'pemasukan')->sum('nominal');
        $totalPengeluaran = $transaksi->where('tipe', 'pengeluaran')->sum('nominal');
        $saldo = $totalPemasukan - $totalPengeluaran;

        // Generate PDF
        $pdf = \PDF::loadView('user.reportRiwayat', [
            'transaksi' => $transaksi,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldo' => $saldo,
            'tanggal' => $tanggal,
            'username' => $user->username,
        ]);

        $filename = 'laporan_riwayat_' . date('Ymd_His') . '.pdf';

        // Return PDF sebagai stream untuk download di Android
        return $pdf->download($filename);
    }

    public function getStatistik(Request $request, $id_user = null)
    {
        if ($response = $this->guardPathUserIdMatch($request, $id_user)) {
            return $response;
        }
        $id_user = $request->user()->id_user;
        $series = app(StatisticService::class)->buildSixMonthSeries((int) $id_user);

        $cashflowSeries = CashflowService::buildSeries((int) $id_user);

        $snapshot = app(BalanceService::class)->getSnapshot((int) $id_user);
        $currentSaldo = $snapshot['real_saldo'] ?? 0;

        return response()->json([
            'success' => true,
            'message' => ($currentSaldo == 0) ? 'Saldo masih kosong' : 'Data statistik berhasil diambil',
            'data' => [
                'labels' => $series['labels'],
                'pemasukan' => $series['pemasukan'],
                'pengeluaran' => $series['pengeluaran'],
                'net' => $series['net'],
                'cashflow_series' => $cashflowSeries,
                'default_cashflow_period' => '30d',
                'budget_kategori' => BudgetKategoriModel::where('id_user', $id_user)->get()->map(function ($budget) {
                    return $this->buildBudgetPayload($budget);
                }),
                'kategori_list' => TransactionModel::where('id_user', $id_user)
                    ->where('tipe', 'pengeluaran')
                    ->select('kategori')
                    ->distinct()
                    ->pluck('kategori'),
                'motivasi' => MotivasiModel::all()
            ]
        ]);
    }

    public function getBudgetKategori(Request $request, $id_user = null)
    {
        if ($response = $this->guardPathUserIdMatch($request, $id_user)) {
            return $response;
        }
        $id_user = $request->user()->id_user;

        $budgets = BudgetKategoriModel::where('id_user', $id_user)->get()->map(function ($budget) {
            return $this->buildBudgetPayload($budget);
        });

        return response()->json([
            'success' => true,
            'data' => $budgets
        ]);
    }

    public function simpanBudgetKategori(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'nullable|numeric',
            'kategori' => 'required|string|max:100',
            'nominal' => 'required|numeric|min:1',
            'periode' => 'required|in:mingguan,bulanan,custom',
            'tanggal_mulai' => 'nullable|required_if:periode,custom|date',
            'tanggal_akhir' => 'nullable|required_if:periode,custom|date|after_or_equal:tanggal_mulai',
        ]);

        if ($response = $this->guardBodyUserIdMatch($request, $validated)) {
            return $response;
        }
        $validated['id_user'] = $request->user()->id_user;

        $budget = BudgetKategoriModel::updateOrCreate(
            [
                'id_user' => $validated['id_user'],
                'kategori' => strtolower(trim($validated['kategori'])),
                'periode' => $validated['periode'],
                'tanggal_mulai' => $validated['periode'] === 'custom' ? $validated['tanggal_mulai'] : null,
            ],
            [
                'nominal' => $validated['nominal'],
                'tanggal_akhir' => $validated['periode'] === 'custom' ? $validated['tanggal_akhir'] : null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Budget kategori "' . ucfirst($budget->kategori) . '" berhasil disimpan',
            'data' => $budget
        ]);
    }

    public function hapusBudgetKategori(Request $request, $id)
    {
        $validated = $request->validate([
            'id_user' => 'nullable|numeric',
            'password' => 'required|string',
        ]);

        if ($response = $this->guardBodyUserIdMatch($request, $validated)) {
            return $response;
        }
        $validated['id_user'] = $request->user()->id_user;
        $user = User::find($validated['id_user']);
        if (!$user || !\Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password yang Anda masukkan salah'
            ], 401);
        }

        $budget = BudgetKategoriModel::where('id', $id)
            ->where('id_user', $validated['id_user'])
            ->first();

        if (!$budget) {
            return response()->json([
                'success' => false,
                'message' => 'Budget tidak ditemukan'
            ], 404);
        }

        $nama = ucfirst($budget->kategori);
        $budget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Budget kategori "' . $nama . '" berhasil dihapus'
        ]);
    }

    /**
     * Reset saldo user (hanya bulan ini) dan hapus riwayat transaksi bulan ini
     */
    public function resetSaldo(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'nullable|numeric',
            'password' => 'required',
        ]);

        if ($response = $this->guardBodyUserIdMatch($request, $validated)) {
            return $response;
        }
        $id_user = $request->user()->id_user;
        $user = User::find($id_user);

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password yang Anda masukkan salah'
            ], 401);
        }

        try {
            $resetResult = app(BalanceResetService::class)->resetCurrentMonth((int) $id_user);

            // Kirim notifikasi konfirmasi reset
            if ($user->fcm_token) {
                try {
                    $firebaseService = app(\App\Services\FirebaseService::class);
                    $firebaseService->sendAdminNotification(
                        $user,
                        "Saldo dan riwayat transaksi bulan ini telah berhasil direset. Mari mulai mencatat lagi! 📈",
                        'saldo_reset'
                    );
                } catch (\Exception $e) {
                    \Log::error('FCM Reset Notification Error: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Saldo dan riwayat transaksi bulan ini berhasil direset',
                'data' => [
                    'id_user' => (int) $id_user,
                    'username' => $user->username,
                    'deleted_transaction_count' => (int) $resetResult['deleted_transaction_count'],
                    'deleted_dream_deposit_count' => (int) $resetResult['deleted_dream_deposit_count'],
                    'saldo' => (string) $resetResult['saldo'],
                    'pemasukan' => (string) $resetResult['pemasukan'],
                    'pengeluaran' => (string) $resetResult['pengeluaran']
                ]
            ]);
        } catch (\Throwable $e) {
            \Log::error('API reset saldo failed', [
                'id_user' => $id_user,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal meriset saldo. Silakan coba lagi.'
            ], 500);
        }
    }


    public function hapusImpian(Request $request, $id_impian)
    {
        $validated = $request->validate([
            'id_user' => 'nullable|numeric',
            'password' => 'required',
        ]);

        if ($response = $this->guardBodyUserIdMatch($request, $validated)) {
            return $response;
        }
        $id_user = $request->user()->id_user;
        $user = User::find($id_user);

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password yang Anda masukkan salah'
            ], 401);
        }

        // cari impian milik user yang login
        $impian = ImpianModel::where('id_impian', $id_impian)
            ->where('id_user', $id_user)
            ->first();

        if (!$impian) {
            return response()->json([
                'success' => false,
                'message' => 'Impian tidak ditemukan atau bukan milik Anda'
            ], 404);
        }

        // hapus file foto bila ada
        if (!empty($impian->foto_barang)) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($impian->foto_barang)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($impian->foto_barang);
            }
        }

        $impian->delete();

        return response()->json([
            'success' => true,
            'message' => 'Impian berhasil dihapus'
        ]);
    }

    private function guardPathUserIdMatch(Request $request, $pathUserId): ?\Illuminate\Http\JsonResponse
    {
        if ($pathUserId === null || $pathUserId === '') {
            return null;
        }

        $actorId = (int) $request->user()->id_user;
        if ((int) $pathUserId !== $actorId) {
            \Log::warning('API path id_user mismatch blocked', [
                'endpoint' => $request->path(),
                'actor_user_id' => $actorId,
                'requested_user_id' => (int) $pathUserId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan mengakses data user lain.'
            ], 403);
        }

        return null;
    }

    private function guardBodyUserIdMatch(Request $request, array $validated): ?\Illuminate\Http\JsonResponse
    {
        if (!array_key_exists('id_user', $validated) || $validated['id_user'] === null || $validated['id_user'] === '') {
            return null;
        }

        $actorId = (int) $request->user()->id_user;
        if ((int) $validated['id_user'] !== $actorId) {
            \Log::warning('API body id_user mismatch blocked', [
                'endpoint' => $request->path(),
                'actor_user_id' => $actorId,
                'requested_user_id' => (int) $validated['id_user'],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan mengakses data user lain.'
            ], 403);
        }

        return null;
    }

    private function buildBudgetPayload(BudgetKategoriModel $budget): array
    {
        $spent = $budget->getSpentAmount();
        $nominal = (float) $budget->nominal;
        $percentage = $budget->getPercentage();

        return [
            'id' => $budget->id,
            'kategori' => $budget->kategori,
            'nominal' => $nominal,
            'periode' => $budget->periode,
            'tanggal_mulai' => $budget->tanggal_mulai ? $budget->tanggal_mulai->toDateString() : null,
            'tanggal_akhir' => $budget->tanggal_akhir ? $budget->tanggal_akhir->toDateString() : null,
            'spent' => $spent,
            'percentage' => $percentage,
            'over' => $budget->isOverBudget(),
            'periode_label' => $budget->getPeriodeLabel(),
        ];
    }

    public function sendFeedback(Request $request)
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
            'is_read' => 0
        ]);

        // Trigger RTDB notification for admin
        $firebaseService = app(FirebaseService::class);
        $firebaseService->notifyNewFeedback([
            'id_user' => $user->id_user,
            'username' => $user->username,
            'subjek' => $feedback->subjek
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Umpan balik berhasil dikirim'
        ]);
    }
}
