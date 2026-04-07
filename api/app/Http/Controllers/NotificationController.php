<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->has('unread') && $request->unread === 'true') {
            $query->unread();
        }

        $notifications = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => NotificationResource::collection($notifications)->response()->getData(true),
                'unread_count' => Notification::where('user_id', $user->id)->unread()->count()
            ]
        ]);
    }

    public function show(string $id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new NotificationResource($notification)
        ]);
    }

    public function markAsRead(string $id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    public function delete(string $id)
    {
        $user = Auth::user();
        $notification = Notification::where('user_id', $user->id)->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    public function unreadCount()
    {
        $user = Auth::user();
        $count = Notification::where('user_id', $user->id)->unread()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }
}
