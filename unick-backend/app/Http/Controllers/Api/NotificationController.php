<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->notifications();

        if ($request->has('unread') && $request->unread) {
            $query->unread();
        }

        $notifications = $query->orderBy('created_at', 'desc')
                              ->paginate($request->get('per_page', 15));

        return response()->json($notifications);
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['is_read' => true]);
        return response()->json($notification);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications()->unread()->update(['is_read' => true]);
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return response()->json(null, 204);
    }
}
