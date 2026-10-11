<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all'); // all | unread

        $query = AppNotification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at');

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(20)->withQueryString();

        $unreadCount = AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->count();

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter' => $filter,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markRead(Request $request, AppNotification $notification)
    {
        if ((int)$notification->user_id !== (int)$request->user()->id) {
            return redirect()->route('notifications.index')->with('error', 'Invalid notification.');
        }

        if (!$notification->read_at) {
            $notification->read_at = now();
            $notification->save();
        }

        return redirect()->route('notifications.index', ['filter' => $request->get('filter', 'all')])
            ->with('success', 'Notification marked as read.');
    }

    public function markAllRead(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return redirect()->route('notifications.index')->with('success', 'All notifications marked as read.');
    }
}
