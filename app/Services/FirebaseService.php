<?php

namespace App\Services;

use App\Models\NotificationHistory;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FirebaseService
{
    protected $database;
    protected $messaging;

    public function __construct()
    {
        try {
            // Path ke service account credentials
            $credentialsPath = storage_path('app/firebase/service-account.json');

            if (!file_exists($credentialsPath)) {
                Log::error('Firebase service account file not found at: ' . $credentialsPath);
                return;
            }

            $factory = (new Factory)->withServiceAccount($credentialsPath)
                ->withDatabaseUri('https://kassaku-8beb0-default-rtdb.asia-southeast1.firebasedatabase.app');

            $this->messaging = $factory->createMessaging();
            $this->database = $factory->createDatabase();
        } catch (\Exception $e) {
            Log::error('Firebase initialization error: ' . $e->getMessage());
        }
    }

    /**
     * Update user balance in Realtime Database
     */
    public function updateUserBalance($userId, $data)
    {
        if (!$this->database)
            return;

        try {
            $path = "users/{$userId}/balance";
            $payload = [
                'saldo' => (float) ($data['saldo'] ?? 0),
                'pemasukan' => (float) ($data['pemasukan'] ?? 0),
                'pengeluaran' => (float) ($data['pengeluaran'] ?? 0),
                'target_pengeluaran' => isset($data['target_pengeluaran']) ? (float) $data['target_pengeluaran'] : null,
                'updated_at' => now()->timestamp
            ];

            $this->database->getReference($path)->update($payload);

            Log::info('RTDB user balance updated', [
                'user_id' => (int) $userId,
                'path' => $path,
                'data' => $payload
            ]);
        } catch (\Exception $e) {
            Log::error('RTDB Update Balance Error: ' . $e->getMessage(), [
                'user_id' => $userId
            ]);
        }
    }

    /**
     * Update user active status in Realtime Database
     */
    public function updateUserStatus($userId, $active)
    {
        if (!$this->database)
            return;

        try {
            $this->database->getReference("users/{$userId}/status")
                ->update([
                    'active' => (int) $active,
                    'updated_at' => now()->timestamp
                ]);
            Log::info('RTDB user status updated', [
                'user_id' => (int) $userId,
                'active' => (int) $active,
            ]);
        } catch (\Exception $e) {
            Log::error('RTDB Update Status Error: ' . $e->getMessage());
        }
    }

    /**
     * Publish account/session events for realtime clients.
     */
    public function notifyUserAccountEvent($userId, $event, $message = null, array $extra = [])
    {
        if (!$this->database) {
            return;
        }

        try {
            $payload = array_merge([
                'event' => $event,
                'message' => $message,
                'timestamp' => now()->timestamp,
                'event_id' => (string) Str::uuid(),
            ], $extra);

            $this->database->getReference("users/{$userId}/account_event")
                ->set($payload);
            Log::info('RTDB account event published', [
                'user_id' => (int) $userId,
                'event' => $event,
            ]);
        } catch (\Exception $e) {
            Log::error('RTDB Account Event Error: ' . $e->getMessage());
        }
    }

    /**
     * Notify admin about unblock request via RTDB
     */
    public function notifyNewUnblockRequest($data)
    {
        if (!$this->database)
            return;

        try {
            $this->database->getReference("admin/unblock_requests")
                ->push([
                    'user_id' => $data['id_user'],
                    'username' => $data['username'],
                    'message' => $data['pesan'],
                    'timestamp' => now()->timestamp,
                    'status' => 'pending'
                ]);
            Log::info('RTDB unblock request published', [
                'user_id' => (int) $data['id_user'],
                'username' => $data['username'],
            ]);
        } catch (\Exception $e) {
            Log::error('RTDB Unblock Request Error: ' . $e->getMessage());
        }
    }

    /**
     * Notify user about unblock response via RTDB
     * Android app listens to this node for realtime updates
     */
    public function notifyUnblockResponse($userId, $status, $message = null)
    {
        if (!$this->database)
            return;

        try {
            $this->database->getReference("users/{$userId}/unblock_response")
                ->set([
                    'status' => $status,
                    'message' => $message,
                    'timestamp' => now()->timestamp,
                ]);
            Log::info('RTDB unblock response published', [
                'user_id' => (int) $userId,
                'status' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('RTDB Unblock Response Error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi ke satu device
     * 
     * @param string $fcmToken FCM token dari device
     * @param string $title Judul notifikasi
     * @param string $body Isi notifikasi
     * @param array $data Custom data payload (optional)
     * @return bool Success status
     */
    public function sendToDevice($fcmToken, $title, $body, $data = [])
    {
        if (!$this->messaging || !$fcmToken) {
            Log::warning('FCM: Cannot send notification - messaging not initialized or token missing');
            return false;
        }

        try {
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(Notification::create($title, $body))
                ->withData($data)
                ->withAndroidConfig([
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'kassaku_notifications',
                        'sound' => 'default',
                    ],
                ])
                ->withApnsConfig([
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                ]);

            $this->messaging->send($message);

            Log::info('FCM notification sent successfully', [
                'title' => $title,
                'token_preview' => substr($fcmToken, 0, 20) . '...'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('FCM send error: ' . $e->getMessage(), [
                'title' => $title,
                'token_preview' => substr($fcmToken, 0, 20) . '...'
            ]);
            return false;
        }
    }

    /**
     * Kirim notifikasi ke multiple devices
     * 
     * @param array $fcmTokens Array of FCM tokens
     * @param string $title Judul notifikasi
     * @param string $body Isi notifikasi
     * @param array $data Custom data payload (optional)
     * @return bool Success status
     */
    public function sendToMultipleDevices($fcmTokens, $title, $body, $data = [])
    {
        if (!$this->messaging || empty($fcmTokens)) {
            Log::warning('FCM: Cannot send multicast - messaging not initialized or tokens missing');
            return false;
        }

        try {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $this->messaging->sendMulticast($message, $fcmTokens);

            Log::info('FCM multicast sent successfully', [
                'title' => $title,
                'recipients' => count($fcmTokens)
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('FCM multicast error: ' . $e->getMessage(), [
                'title' => $title,
                'recipients' => count($fcmTokens)
            ]);
            return false;
        }
    }

    /**
     * Kirim notifikasi transaksi baru
     */
    public function sendTransactionNotification($user, $type, $amount)
    {
        $emoji = $type === 'pemasukan' ? '💰' : '💸';
        $title = "{$emoji} Transaksi {$type}";
        $body = "Rp " . number_format($amount, 0, ',', '.');

        $payload = [
            'type' => 'transaction',
            'transaction_type' => $type,
            'amount' => $amount,
            'message' => $body,
            'timestamp' => now()->toDateTimeString()
        ];

        // Always store history for in-app inbox
        $this->storeNotificationHistory($user->id_user, 'transaction', $title, $body, $payload);

        if (!$user->fcm_token) {
            return false;
        }

        return $this->sendToDevice(
            $user->fcm_token,
            $title,
            $body,
            $payload
        );
    }

    /**
     * Kirim notifikasi reminder impian/tabungan
     */
    public function sendDreamReminderNotification($user, $dreamName, $progress)
    {
        $title = "🎯 Reminder Target Tabungan";
        $body = "Kamu sudah mencapai {$progress}% dari target: {$dreamName}";

        $payload = [
            'type' => 'reminder',
            'dream_name' => $dreamName,
            'progress' => $progress
        ];

        // Always store history for in-app inbox
        $this->storeNotificationHistory($user->id_user, 'dream_reminder', $title, $body, $payload);

        if (!$user->fcm_token) {
            return false;
        }

        return $this->sendToDevice(
            $user->fcm_token,
            $title,
            $body,
            $payload
        );
    }

    public function sendFinancialReminderNotification($user, $category, $title, $body, array $data = [])
    {
        $payload = array_merge([
            'type' => 'financial_reminder',
            'reminder_category' => $category,
            'timestamp' => now()->toDateTimeString(),
        ], $data);

        // Always store history for in-app inbox
        $this->storeNotificationHistory($user->id_user, $category, $title, $body, $payload);

        if (!$user->fcm_token) {
            return false;
        }

        return $this->sendToDevice(
            $user->fcm_token,
            $title,
            $body,
            $payload
        );
    }

    /**
     * Kirim notifikasi dari admin
     */
    public function sendAdminNotification($user, $message, $action = null)
    {
        $title = "📢 Notifikasi Admin";
        $accountStatus = null;
        $forceLogout = false;

        if ($action === 'account_blocked') {
            $accountStatus = 'blocked';
            $forceLogout = true;
        } elseif (in_array($action, ['account_unblocked', 'unblock_approved'], true)) {
            $accountStatus = 'active';
        }

        $payload = [
            'type' => 'admin',
            'action' => $action,
            'account_status' => $accountStatus,
            'force_logout' => $forceLogout ? 'true' : 'false',
            'message' => $message,
            'timestamp' => now()->toDateTimeString()
        ];

        // Always store history for in-app inbox
        $this->storeNotificationHistory($user->id_user, 'admin', $title, $message, $payload);

        if (!$user->fcm_token) {
            return false;
        }

        return $this->sendToDevice(
            $user->fcm_token,
            $title,
            $message,
            $payload
        );
    }

    private function storeNotificationHistory($userId, $category, $title, $body, array $payload = [])
    {
        try {
            NotificationHistory::create([
                'id_user' => $userId,
                'category' => $category,
                'title' => $title,
                'body' => $body,
                'payload' => !empty($payload) ? json_encode($payload) : null,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store notification history: ' . $e->getMessage(), [
                'user_id' => $userId,
                'category' => $category,
            ]);
        }
    }
}
