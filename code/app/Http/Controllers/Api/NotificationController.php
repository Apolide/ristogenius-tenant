<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Restituisce le notifiche non lette dell'utente autenticato.
     */
    public function unread()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Non autenticato'], 401);
        }

        $notifications = $user->unreadNotifications()->orderBy('notifications.created_at', 'desc')->limit(4)->get();

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'url' => $notification->url,
                    'type' => $notification->type, // Per differenziare le icone
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }),
        ]);
    }
}

