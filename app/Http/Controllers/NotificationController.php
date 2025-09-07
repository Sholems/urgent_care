<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\AppUser;
use App\Models\Doctor;

class NotificationController extends Controller
{
    /**
     * Retrieve notifications for the given model.
     *
     * Some legacy records may store the notifiable type using the model's
     * class basename instead of the fully qualified class name. This helper
     * queries for both representations so existing notifications are returned
     * regardless of how the type was persisted.
     */
    protected function baseQuery($id, string $modelClass)
    {
        return Notification::where('notifiable_id', $id)
            ->whereIn('notifiable_type', [$modelClass, class_basename($modelClass)])
            ->orderBy('created_at', 'desc');
    }

    /**
     * Display a listing of the resource for a given user.
     */
    public function index(\Illuminate\Http\Request $request, $userId)
    {
        $perPage = (int) $request->query('per_page', 15);
        $page    = (int) $request->query('page', 1);
        $perPage = max(1, min($perPage, 100));

        $paginator = $this->baseQuery($userId, AppUser::class)
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->paginatedResponse($paginator);
    }

    /**
     * Display a listing of notifications for a given doctor.
     */
    public function doctor(\Illuminate\Http\Request $request, $doctorId)
    {
        $perPage = (int) $request->query('per_page', 15);
        $page    = (int) $request->query('page', 1);
        $perPage = max(1, min($perPage, 100));

        $paginator = $this->baseQuery($doctorId, Doctor::class)
            ->paginate($perPage, ['*'], 'page', $page);

        return $this->paginatedResponse($paginator);
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
