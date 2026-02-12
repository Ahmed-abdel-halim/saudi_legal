<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\ServicePurchase;

/**
 * Notification Controller
 * 
 * Handles notifications for both Expert and Company users
 * Extends BaseNotificationController for shared functionality
 */
class NotificationController extends BaseNotificationController
{
    /**
     * Get role-based notifications for the user
     */
    protected function getRoleBasedNotifications($user)
    {
        $cacheKey = $this->getCacheKey($user, 'notifications');

        return Cache::remember($cacheKey, $this->cacheDuration, function() use ($user) {
            if ($user->role === 'expert') {
                return $this->getExpertNotifications($user);
            } elseif ($user->role === 'company') {
                return $this->getCompanyNotifications($user);
            }

            // Default: get standard notifications only
            return $this->getStandardNotifications($user);
        });
    }

    /**
     * Get unread count for the user
     */
    protected function getUnreadCount($user)
    {
        $cacheKey = $this->getCacheKey($user, 'unread_count');

        return Cache::remember($cacheKey, $this->cacheDuration, function() use ($user) {
            $dbUnread = $user->unreadNotifications()->count();

            // Add pending requests for experts
            if ($user->role === 'expert') {
                $pendingRequests = ServicePurchase::forExpert($user->id)
                    ->pending()
                    ->count();
                
                return $dbUnread + $pendingRequests;
            }

            return $dbUnread;
        });
    }

    /**
     * Get notifications for Expert users
     */
    protected function getExpertNotifications($user)
    {
        // 1. Get standard notifications (excluding service requests)
        $dbNotifications = $user->notifications()
            ->take($this->maxNotifications)
            ->get();

        $otherNotifications = $this->filterServiceRequestNotifications($dbNotifications)
            ->map(fn($n) => $this->formatNotification($n, 'database'))
            ->filter();

        // 2. Get pending service requests (Virtual Notifications with rich data)
        $pendingRequests = ServicePurchase::with(['client', 'service'])
            ->forExpert($user->id)
            ->pending()
            ->latest()
            ->take($this->maxNotifications)
            ->get()
            ->map(function ($purchase) {
                try {
                    return [
                        'id' => 'req_' . $purchase->id,
                        'source_id' => $purchase->id,
                        'type' => 'NewServiceRequestNotification',
                        'data' => $purchase->getNotificationData(),
                        'read_at' => null,
                        'created_at' => $purchase->created_at ? $purchase->created_at->diffForHumans() : 'Just now',
                        'created_at_human' => $purchase->created_at ? $purchase->created_at->diffForHumans() : 'Just now',
                        'timestamp' => $purchase->created_at ? $purchase->created_at->timestamp : time(),
                    ];
                } catch (\Exception $e) {
                    \Log::warning('Failed to format service purchase notification', [
                        'purchase_id' => $purchase->id,
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            })
            ->filter();

        // 3. Merge and sort by timestamp
        return $pendingRequests
            ->merge($otherNotifications)
            ->sortByDesc('timestamp')
            ->values()
            ->take($this->maxNotifications);
    }

    /**
     * Get notifications for Company users
     */
    protected function getCompanyNotifications($user)
    {
        // Get standard notifications for company
        $dbNotifications = $user->notifications()
            ->take($this->maxNotifications)
            ->get();

        return $dbNotifications
            ->map(fn($n) => $this->formatNotification($n, 'database'))
            ->filter()
            ->sortByDesc('timestamp')
            ->values()
            ->take($this->maxNotifications);
    }

    /**
     * Get standard notifications (fallback for other roles)
     */
    protected function getStandardNotifications($user)
    {
        $dbNotifications = $user->notifications()
            ->take($this->maxNotifications)
            ->get();

        return $dbNotifications
            ->map(fn($n) => $this->formatNotification($n, 'database'))
            ->filter()
            ->sortByDesc('timestamp')
            ->values()
            ->take($this->maxNotifications);
    }
}
