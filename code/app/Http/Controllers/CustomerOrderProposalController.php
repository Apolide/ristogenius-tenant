<?php

namespace App\Http\Controllers;

use App\Services\PublicOrders\OrderProposalService;
use Illuminate\Http\Request;

class CustomerOrderProposalController extends Controller
{
    public function show(Request $request, string $token)
    {
        $proposal = app(OrderProposalService::class)->find($token);
        $choice = in_array($request->query('choice'), ['accept', 'reject'], true) ? $request->query('choice') : null;

        return response()->view('public-orders.proposal', compact('proposal', 'token', 'choice'))
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function respond(Request $request, string $token)
    {
        $request->validate(['choice' => 'required|in:accept,reject']);
        app(OrderProposalService::class)->respond($token, $request->input('choice'));

        return redirect()->route('public-orders.proposal', ['token' => $token])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }
}
