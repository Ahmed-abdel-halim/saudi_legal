<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Base Notification Controller
 * 
 * Provides shared notification logic for both Expert and Company dashboards.
 * Handles role-based filtering, caching, and error handling.
 */
abstract class BaseNotificationController extends Controller
{
    /**
     * Cache duration in seconds (5 minutes)
     */
    protected int $cacheDuration = 300;

    /**
     * Maximum notifications to fetch
     */
    protected int $maxNotifications = 50;

    /**
     * Get notifications for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }

            // Get role-specific notifications
            $notifications = $this->getRoleBasedNotifications($user);
            $unreadCount = $this->getUnreadCount($user);

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Notification fetch error', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse(
                'Failed to fetch notifications',
                500,
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return $this->errorResponse('User not authenticated', 401);
            }

            $count = $this->getUnreadCount($user);

            return response()->json([
                'success' => true,
                'count' => $count,
            ]);

        } catch (\Exception $e) {
            Log::error('Unread count error', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'count' => 0,
            ]);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $notification = $user->notifications()
                ->where('id', $id)
                ->first();

            if ($notification) {
                $notification->markAsRead();
                $this->clearNotificationCache($user);
                
                return response()->json(['success' => true]);
            }

            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);

        } catch (\Exception $e) {
            Log::error('Mark as read error', [
                'notification_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();
            $user->unreadNotifications->markAsRead();
            $this->clearNotificationCache($user);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Mark all as read error', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Get role-based notifications (to be implemented by child classes)
     */
    abstract protected function getRoleBasedNotifications($user);

    /**
     * Get unread count (to be implemented by child classes)
     */
    abstract protected function getUnreadCount($user);

    /**
     * Format notification for API response
     */
    protected function formatNotification($notification, string $type = 'database'): ?array
    {
        try {
            if ($type === 'database') {
                return [
                    'id' => $notification->id,
                    'source_id' => $notification->data['id'] ?? null,
                    'type' => class_basename($notification->type),
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at ? $notification->created_at->diffForHumans() : 'Now',
                    'created_at_human' => $notification->created_at ? $notification->created_at->diffForHumans() : 'Now',
                    'timestamp' => $notification->created_at ? $notification->created_at->timestamp : time(),
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::warning('Notification formatting error', [
                'notification_id' => $notification->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Get cache key for user notifications
     */
    protected function getCacheKey($user, string $suffix = 'notifications'): string
    {
        return "user_{$user->id}_{$user->role}_{$suffix}";
    }

    /**
     * Clear notification cache for user
     */
    protected function clearNotificationCache($user): void
    {
        Cache::forget($this->getCacheKey($user, 'notifications'));
        Cache::forget($this->getCacheKey($user, 'unread_count'));
    }

    /**
     * Return error response
     */
    protected function errorResponse(string $message, int $code = 500, ?string $debug = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($debug) {
            $response['debug'] = $debug;
        }

        return response()->json($response, $code);
    }

    /**
     * Filter out service request notifications (handled separately)
     */
    protected function filterServiceRequestNotifications($notifications)
    {
        return $notifications->filter(function($n) {
            $type = is_array($n) ? ($n['type'] ?? '') : $n->type;
            return !str_contains($type, 'ServiceRequest') && 
                   !str_contains($type, 'NewServiceRequestNotification');
        });
    }
}
