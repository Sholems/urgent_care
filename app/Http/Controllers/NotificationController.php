<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\AppUser;
use App\Models\Doctor;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource for a given user.
     */
    public function index($userId)
    {
        // Some legacy records may store the notifiable type using the class
        // basename ("AppUser") instead of the fully qualified class name.
        // Query for both representations so existing notifications are
        // returned regardless of how the type was persisted.
        $appUserClass = AppUser::class;

        $notifications = Notification::where('notifiable_id', $userId)
            ->whereIn('notifiable_type', [$appUserClass, class_basename($appUserClass)])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
        ]);
    }

    /**
     * Display a listing of notifications for a given doctor.
     */
    public function doctor($doctorId)
    {
        $doctorClass = Doctor::class;

        $notifications = Notification::where('notifiable_id', $doctorId)
            ->whereIn('notifiable_type', [$doctorClass, class_basename($doctorClass)])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
        ]);
    }

    /**
     * Mark the specified notification as read.
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read',
        ]);
    }
}
