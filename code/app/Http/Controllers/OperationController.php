<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperationController extends Controller
{
    /**
     * Mostra tutte le notifiche dell'utente.
     */
    public function index()
    {
        $user = Auth::user();
        $operations = $user->operations()->orderBy('operations.created_at', 'desc')->paginate(15);
       

        return view('operations.index', compact('operations'));
    }

    /**
     * Segna tutte le notifiche dell'utente come lette.
     */
    public function markAllRead(Request $request)
    {
        $user = Auth::user();
        $user->operations()->updateExistingPivot(
            $user->operations()->pluck('operations.id')->toArray(),
            ['read_at' => now()]
        );

        return response()->json(['status' => 'success']);
    }
}
