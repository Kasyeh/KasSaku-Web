<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ===========================
// PUBLIC ROUTES (Tanpa Auth)
// ===========================
Route::post('/login', [ApiController::class, 'login'])->middleware('throttle:api-login');
Route::post('/register', [ApiController::class, 'actionRegister'])->middleware('throttle:api-register');
Route::post('/unblock-request', [ApiController::class, 'submitUnblockRequest']);
Route::post('/forgot-password/send-otp', [ApiController::class, 'apiSendOtp']);
Route::post('/forgot-password/reset', [ApiController::class, 'apiResetPassword']);
Route::post('/auth/google/android', [\App\Http\Controllers\SocialController::class, 'loginWithGoogleAndroid']);

// ===========================
// PROTECTED ROUTES (Butuh Sanctum Token)
// ===========================
Route::middleware(['auth:sanctum', 'active.api'])->group(function () {

    // User Info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Avatar
    Route::post('/user/profile/avatar/upload', [\App\Http\Controllers\AvatarController::class, 'upload']);
    Route::post('/user/profile/avatar/predefined', [\App\Http\Controllers\AvatarController::class, 'setPredefined']);
    Route::post('/user/profile/avatar/remove', [\App\Http\Controllers\AvatarController::class, 'remove']);


    // Saldo & Balance
    Route::get('/me/saldo', [ApiController::class, 'getSaldo']);
    Route::get('/user/{id}/saldo', [ApiController::class, 'getSaldo']);
    Route::post('/target-pengeluaran/simpan', [ApiController::class, 'simpanTargetPengeluaran']);
    Route::post('/user/reset-saldo', [ApiController::class, 'resetSaldo']);
    Route::post('/user/profile/update-email', [UserController::class, 'updateEmail']);

    // Transaksi
    Route::get('/me/riwayat', [ApiController::class, 'getRiwayat']);
    Route::get('/riwayat/{id_user}', [ApiController::class, 'getRiwayat']);
    Route::post('/pemasukan/tambah', [ApiController::class, 'tambahPemasukan']);
    Route::post('/pengeluaran/tambah', [ApiController::class, 'tambahPengeluaran']);
    Route::get('/me/riwayat/export-pdf', [ApiController::class, 'exportPdf']);
    Route::get('/riwayat/{id_user}/export-pdf', [ApiController::class, 'exportPdf']);

    // Impian
    Route::get('/me/impian', [ApiController::class, 'getImpian']);
    Route::get('/impian/{id_user}', [ApiController::class, 'getImpian']);
    Route::post('/impian/tambah', [ApiController::class, 'tambahImpian']);
    Route::post('/impian/{id_impian}/setoran', [ApiController::class, 'setorImpian']);
    Route::post('/impian/hapus/{id_impian}', [ApiController::class, 'hapusImpian']);

    // Statistik
    Route::get('/me/statistik', [ApiController::class, 'getStatistik']);
    Route::get('/user/{id}/statistik', [ApiController::class, 'getStatistik']);

    // Budget Kategori
    Route::get('/me/budget-kategori', [ApiController::class, 'getBudgetKategori']);
    Route::get('/user/{id}/budget-kategori', [ApiController::class, 'getBudgetKategori']);
    Route::post('/user/budget-kategori/simpan', [ApiController::class, 'simpanBudgetKategori']);
    Route::post('/user/budget-kategori/hapus/{id}', [ApiController::class, 'hapusBudgetKategori']);

    // FCM Notification Routes
    Route::post('/fcm-token', [NotificationController::class, 'saveFcmToken']);
    Route::get('/me/notifications', [NotificationController::class, 'getNotificationHistory']);
    Route::post('/me/notifications/read-all', [NotificationController::class, 'markAllNotificationsAsRead']);
    Route::get('/notification/preferences', [NotificationController::class, 'getReminderPreferences']);
    Route::post('/notification/preferences', [NotificationController::class, 'saveReminderPreferences']);
    Route::post('/notification/test', [NotificationController::class, 'sendTestNotification']);
    Route::post('/notification/transaction', [NotificationController::class, 'sendTransactionNotification']);
    Route::post('/notification/admin', [NotificationController::class, 'sendAdminNotification']);
});
