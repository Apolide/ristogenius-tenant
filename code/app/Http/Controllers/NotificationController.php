<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mostra tutte le notifiche dell'utente.
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->orderBy('notifications.created_at', 'desc')->paginate(15);
       

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Segna tutte le notifiche dell'utente come lette.
     */
    public function markAllRead(Request $request)
    {
        $user = Auth::user();
        $user->notifications()->updateExistingPivot(
            $user->notifications()->pluck('notifications.id')->toArray(),
            ['read_at' => now()]
        );

        return response()->json(['status' => 'success']);
    }
}
