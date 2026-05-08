<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use App\Services\FinancialReminderService;
use App\Models\NotificationHistory;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function getReminderPreferences(Request $request, FinancialReminderService $financialReminderService)
    {
        $preference = $financialReminderService->resolvePreference($request->user());

        return response()->json([
            'success' => true,
            'data' => $this->formatReminderPreference($preference),
        ]);
    }

    public function saveReminderPreferences(Request $request, FinancialReminderService $financialReminderService)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id_user',
            'reminders_enabled' => 'nullable|boolean',
            'daily_reminder_enabled' => 'nullable|boolean',
            'daily_reminder_hour' => 'required|integer|min:0|max:23',
            'budget_alert_enabled' => 'nullable|boolean',
            'budget_alert_threshold' => 'required|integer|min:50|max:100',
            'dream_reminder_enabled' => 'nullable|boolean',
            'dream_inactive_days' => 'required|integer|min:1|max:30',
        ]);

        $targetUser = $this->resolveUserBoundTarget($request);
        if ($targetUser instanceof \Illuminate\Http\JsonResponse) {
            return $targetUser;
        }

        $preference = NotificationPreference::updateOrCreate(
            ['id_user' => $targetUser->id_user],
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

        return response()->json([
            'success' => true,
            'message' => 'Reminder preferences updated successfully',
            'data' => $this->formatReminderPreference($preference),
        ]);
    }

    public function getNotificationHistory(Request $request)
    {
        $items = NotificationHistory::where('id_user', $request->user()->id_user)
            ->orderByDesc('sent_at')
            ->limit(30)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => (int) $item->id,
                    'category' => $item->category,
                    'title' => $item->title,
                    'body' => $item->body,
                    'sent_at' => optional($item->sent_at)->toIso8601String(),
                    'sent_at_human' => optional($item->sent_at)->diffForHumans(),
                    'read' => $item->read_at !== null,
                    'accent' => $this->resolveNotificationAccent($item->category),
                    'icon' => $this->resolveNotificationIcon($item->category),
                    'excerpt' => Str::limit($item->body, 110),
                ];
            })
            ->values();

        $unreadCount = NotificationHistory::where('id_user', $request->user()->id_user)
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

    public function markAllNotificationsAsRead(Request $request)
    {
        NotificationHistory::where('id_user', $request->user()->id_user)
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

    /**
     * Save FCM token from Android app
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveFcmToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'user_id' => 'nullable|exists:users,id_user'
        ]);

        try {
            $targetUser = $this->resolveUserBoundTarget($request);
            if ($targetUser instanceof \Illuminate\Http\JsonResponse) {
                return $targetUser;
            }

            $targetUser->fcm_token = $request->token;
            $targetUser->save();

            Log::info('FCM token saved for user', [
                'actor_user_id' => $request->user()->id_user,
                'target_user_id' => $targetUser->id_user,
                'token_preview' => substr($request->token, 0, 20) . '...'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token saved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving FCM token: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save FCM token'
            ], 500);
        }
    }

    /**
     * Send test notification to user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTestNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id_user'
        ]);

        try {
            $targetUser = $this->resolveUserBoundTarget($request);
            if ($targetUser instanceof \Illuminate\Http\JsonResponse) {
                return $targetUser;
            }

            if (!$targetUser->fcm_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have FCM token registered'
                ], 400);
            }

            $result = $this->firebaseService->sendToDevice(
                $targetUser->fcm_token,
                'Test Notification',
                'Ini adalah notifikasi test dari Kassaku! 🎉',
                [
                    'type' => 'test',
                    'timestamp' => now()->toDateTimeString()
                ]
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Notification sent successfully' : 'Failed to send notification'
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending test notification: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error sending notification'
            ], 500);
        }
    }

    /**
     * Send notification transaksi (called from transaction create/update)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendTransactionNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id_user',
            'type' => 'required|in:pemasukan,pengeluaran',
            'amount' => 'required|numeric'
        ]);

        try {
            $targetUser = $this->resolveUserBoundTarget($request);
            if ($targetUser instanceof \Illuminate\Http\JsonResponse) {
                return $targetUser;
            }

            if (!$targetUser->fcm_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have FCM token'
                ], 400);
            }

            $result = $this->firebaseService->sendTransactionNotification(
                $targetUser,
                $request->type,
                $request->amount
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Transaction notification sent' : 'Failed to send notification'
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending transaction notification: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error sending notification'
            ], 500);
        }
    }

    /**
     * Send admin notification to user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendAdminNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id_user',
            'message' => 'required|string',
            'action' => 'nullable|string'
        ]);

        try {
            $actor = $request->user();
            $targetId = $request->input('user_id', $actor->id_user);
            $targetUser = User::where('id_user', $targetId)->first();

            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            if ($targetUser->id_user !== $actor->id_user && $actor->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak diizinkan mengirim notifikasi ke user lain.'
                ], Response::HTTP_FORBIDDEN);
            }

            if (!$targetUser->fcm_token) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have FCM token'
                ], 400);
            }

            $result = $this->firebaseService->sendAdminNotification(
                $targetUser,
                $request->message,
                $request->action
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Admin notification sent' : 'Failed to send notification'
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending admin notification: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error sending notification'
            ], 500);
        }
    }

    /**
     * Resolve user target bound to authenticated user to avoid IDOR.
     *
     * @return \App\Models\User|\Illuminate\Http\JsonResponse
     */
    private function resolveUserBoundTarget(Request $request)
    {
        $actor = $request->user();
        $requestedId = $request->input('user_id', $actor->id_user);

        if ((int) $requestedId !== (int) $actor->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan mengakses data user lain.'
            ], Response::HTTP_FORBIDDEN);
        }

        return $actor;
    }

    private function formatReminderPreference(NotificationPreference $preference): array
    {
        return [
            'reminders_enabled' => (bool) $preference->reminders_enabled,
            'daily_reminder_enabled' => (bool) $preference->daily_reminder_enabled,
            'daily_reminder_hour' => (int) $preference->daily_reminder_hour,
            'budget_alert_enabled' => (bool) $preference->budget_alert_enabled,
            'budget_alert_threshold' => (int) $preference->budget_alert_threshold,
            'dream_reminder_enabled' => (bool) $preference->dream_reminder_enabled,
            'dream_inactive_days' => (int) $preference->dream_inactive_days,
        ];
    }

    private function resolveNotificationAccent(string $category): string
    {
        switch ($category) {
            case 'transaction':
                return 'emerald';
            case 'admin':
                return 'amber';
            case 'budget_alert':
                return 'rose';
            case 'daily_input':
                return 'sky';
            case 'dream_progress':
            case 'dream_reminder':
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
                return 'campaign';
            case 'budget_alert':
                return 'warning';
            case 'daily_input':
                return 'edit_note';
            case 'dream_progress':
            case 'dream_reminder':
                return 'auto_awesome';
            default:
                return 'notifications';
        }
    }
}
