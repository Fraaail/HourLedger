<?php

namespace App\Http\Controllers;

use App\Models\EntryNotification;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * List all notifications.
     */
    public function index(): Response
    {
        $notifications = EntryNotification::orderBy('is_read', 'asc')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'date' => $notification->date->toDateString(),
                    'formatted_date' => $notification->date->format('M j, Y'),
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->toISOString(),
                ];
            });

        $unreadNotificationCount = EntryNotification::unread()->count();

        return Inertia::render('notifications', [
            'notifications' => $notifications,
            'unread_notification_count' => $unreadNotificationCount,
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(EntryNotification $notification): RedirectResponse
    {
        $notification->markAsRead();

        return back();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): RedirectResponse
    {
        EntryNotification::unread()->update(['is_read' => true]);

        return back();
    }
}
