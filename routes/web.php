<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MotivasiController;
use App\Http\Controllers\ImpianController;
use App\Http\Controllers\RiwayatController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// AUTH ROUTES (GUEST ONLY)
Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'login'])->name('login');
    Route::post('actionLogin', [LoginController::class, 'actionLogin'])->name('actionLogin');
    Route::get('register', [RegisterController::class, 'register'])->name('register');
    Route::post('register/action', [RegisterController::class, 'actionRegister'])->name('actionRegister');

    // Forgot Password OTP Flow
    Route::get('forgot-password', [\App\Http\Controllers\ForgotPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('forgot-password', [\App\Http\Controllers\ForgotPasswordController::class, 'sendOtp'])->name('password.email');
    Route::get('reset-password', [\App\Http\Controllers\ForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('reset-password', [\App\Http\Controllers\ForgotPasswordController::class, 'resetPassword'])->name('password.update');

    // Google Auth
    Route::get('auth/google', [\App\Http\Controllers\SocialController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('auth/google/callback', [\App\Http\Controllers\SocialController::class, 'handleGoogleCallback']);
});

Route::get('/actionLogout', [LoginController::class, 'actionLogout'])->name('actionLogout')->middleware('auth');


//Admin
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard/admin', [AdminController::class, 'dashAdmin'])->name('admin.dashboard');
    Route::get('/list_user', [AdminController::class, 'list_user'])->name('list_user');
    Route::post('/admin/verify-password', [AdminController::class, 'verifyPassword'])->name('admin.verify_password');
    Route::get('/list_user/{id}', [AdminController::class, 'cekUser'])->name('cekUser');
    Route::post('/admin/user/block/{id}', [AdminController::class, 'blockUser'])->name('admin.user.block');
    Route::post('/admin/user/unblock/{id}', [AdminController::class, 'unblockUser'])->name('admin.user.unblock');
    Route::post('/admin/user/hapus/{id}', [AdminController::class, 'hapusUser'])->name('admin.user.hapus');
    Route::get('/admin/permintaan-unblock', [AdminController::class, 'permintaanUnblock'])->name('admin.permintaan_unblock');
    Route::get('/admin/realtime/pending-unblock', [AdminController::class, 'pendingUnblockFeed'])->name('admin.realtime.pending_unblock');
    Route::post('/admin/permintaan-unblock/proses/{id}', [AdminController::class, 'prosesUnblock'])->name('admin.proses_unblock');
    Route::post('/admin/permintaan-unblock/hapus/{id}', [AdminController::class, 'hapusPermintaanUnblock'])->name('admin.hapus_permintaan_unblock');
    Route::post('/admin/permintaan-unblock/bulk-hapus', [AdminController::class, 'bulkHapusPermintaanUnblock'])->name('admin.bulk_hapus_permintaan_unblock');
    // Admin/motivasi
    Route::get('/motivasi', [MotivasiController::class, 'motivasi'])->name('motivasi');
    Route::get('/motivasi/tambah', [MotivasiController::class, 'tambahMotivasi'])->name('tambahMotivasi');
    Route::post('/motivasi/simpan', [MotivasiController::class, 'simpanMotivasi'])->name('simpanMotivasi');
    Route::post('/motivasi/hapus/{id}', [MotivasiController::class, 'hapusMotivasi'])->name('hapusMotivasi');
    Route::get('/motivasi/{id}/edit', [MotivasiController::class, 'editMotivasi'])->name('motivasi.edit');
    Route::post('/motivasi/{id}/update', [MotivasiController::class, 'updateMotivasi'])->name('motivasi.update');
});
//User
Route::middleware(['auth', 'user'])->group(function () {
    Route::get('/user/home', [UserController::class, 'homeUser'])->name('homeUser');
    Route::get('/user/realtime/account-status', [UserController::class, 'accountStatus'])->name('user.realtime.account_status');
    Route::get('/user/statistik', [UserController::class, 'statUser'])->name('statUser');
    Route::get('/user/statistik/snapshot', [UserController::class, 'statistikSnapshot'])->name('user.statistik.snapshot');
    Route::get('/user/profile', [UserController::class, 'profile'])->name('profileUser');
    Route::get('/user/riwayat', [RiwayatController::class, 'riwayatUser'])->name('riwayatUser');

    Route::post('/user/simpanPemasukan', [UserController::class, 'simpanPemasukan'])->name('simpanPemasukan');
    Route::post('/user/simpanPengeluaran', [UserController::class, 'simpanPengeluaran'])->name('simpanPengeluaran');
    Route::post('/user/simpanTargetPengeluaran', [UserController::class, 'simpanTargetPengeluaran'])->name('simpanTargetPengeluaran');
    Route::post('/user/simpanBudgetKategori', [UserController::class, 'simpanBudgetKategori'])->name('simpanBudgetKategori');
    Route::post('/user/hapusBudgetKategori/{id}', [UserController::class, 'hapusBudgetKategori'])->name('hapusBudgetKategori');
    Route::post('/user/reset-saldo', [UserController::class, 'resetSaldo'])->name('resetSaldo');
    Route::post('/user/reminder-preferences', [UserController::class, 'simpanReminderPreference'])->name('user.reminder_preferences');
    Route::get('/user/notifications', [UserController::class, 'getNotificationHistory'])->name('user.notifications.index');
    Route::post('/user/notifications/read-all', [UserController::class, 'markNotificationHistoryAsRead'])->name('user.notifications.read_all');
    Route::post('/user/profile/update-email', [UserController::class, 'updateEmail'])->name('user.update_email');

    // Avatar Routes
    Route::post('/user/profile/avatar/upload', [\App\Http\Controllers\AvatarController::class, 'upload'])->name('user.avatar.upload');
    Route::post('/user/profile/avatar/predefined', [\App\Http\Controllers\AvatarController::class, 'setPredefined'])->name('user.avatar.predefined');
    Route::post('/user/profile/avatar/remove', [\App\Http\Controllers\AvatarController::class, 'remove'])->name('user.avatar.remove');

    Route::get('/user/impian', [ImpianController::class, 'impianUser'])->name('impianUser');
    Route::get('/user/impian/tambah', [ImpianController::class, 'tambahImpian'])->name('tambahImpian');
    Route::post('/user/impian/simpan', [ImpianController::class, 'simpanImpian'])->name('simpanImpian');
    Route::post('/user/impian/{id_impian}/setoran', [ImpianController::class, 'setorImpian'])->name('setorImpian');
    Route::post('/user/impian/hapus/{id}', [ImpianController::class, 'hapusImpian'])->name('hapusImpian');

    Route::get('/user/laporan/pdf', [UserController::class, 'exportPdf'])->name('laporan.pdf');
});

Route::post('/minta-unblock', [UserController::class, 'mintaUnblock'])->name('mintaUnblock');
Route::get('/cek-php', function () {
    return [
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
    ];
});


//Transaksi
