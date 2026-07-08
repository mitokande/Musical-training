<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Student-facing notification centre (Laravel database notifications). */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Capture which notifications are still unread *before* marking them read,
        // so this visit can highlight them (they become "seen"/dimmed next time).
        $freshIds = $user->unreadNotifications()->pluck('id');

        $notifications = $user->notifications()->latest()->paginate(30);

        // Opening the centre clears the navbar bell badge.
        $user->unreadNotifications->markAsRead();

        return view('notifications', [
            'notifications' => $notifications,
            'freshIds' => $freshIds,
        ]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
