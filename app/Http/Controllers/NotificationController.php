<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Show all notifications — DON'T mark as read yet
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    // Mark a single notification as read
    public function markAsRead(string $id)
    {
        $notification = auth()->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return back();
    }

    // Mark all as read
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }

    // Delete a notification
    public function destroy(string $id)
    {
        auth()->user()
            ->notifications()
            ->findOrFail($id)
            ->delete();

        return back()->with('status', 'Notification deleted.');
    }

    // Clear all notifications
    public function clearAll()
    {
        auth()->user()->notifications()->delete();

        return back()->with('status', 'All notifications cleared.');
    }
}