<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\PermintaanUnblockModel;
use App\Models\NotificationHistory;
use App\Models\FeedbackModel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Share pending unblock request count with admin master view
        View::composer('template.master', function ($view) {
            $pendingCount = PermintaanUnblockModel::where('status', 'pending')->count();
            $latestPendingRequest = PermintaanUnblockModel::where('status', 'pending')
                ->orderByDesc('created_at')
                ->first();
            $latestPendingTimestamp = (int) optional($latestPendingRequest?->created_at)->timestamp;

            $pendingFeedbackCount = 0;
            $latestPendingFeedbackTimestamp = 0;
            if (Schema::hasTable('tb_feedback')) {
                $pendingFeedbackCount = FeedbackModel::where('is_read', 0)->count();
                $latestPendingFeedback = FeedbackModel::where('is_read', 0)
                    ->orderByDesc('created_at')
                    ->first();
                $latestPendingFeedbackTimestamp = (int) optional($latestPendingFeedback?->created_at)->timestamp;
            }

            $view->with('pendingUnblockCount', $pendingCount);
            $view->with('pendingUnblockLatestTimestamp', $latestPendingTimestamp);
            $view->with('pendingFeedbackCount', $pendingFeedbackCount);
            $view->with('pendingFeedbackLatestTimestamp', $latestPendingFeedbackTimestamp);
        });

        View::composer('template.masteru', function ($view) {
            $user = auth()->user();
            $unreadNotificationCount = 0;

            if ($user && $user->role === 'user' && Schema::hasTable('tb_notification_histories')) {
                $unreadNotificationCount = NotificationHistory::where('id_user', $user->id_user)
                    ->whereNull('read_at')
                    ->count();
            }

            $view->with('unreadNotificationCount', $unreadNotificationCount);
        });
    }
}
