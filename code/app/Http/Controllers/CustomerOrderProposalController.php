<?php

namespace App\Http\Controllers;

use App\Services\PublicOrders\OrderProposalService;
use Illuminate\Http\Request;

class CustomerOrderProposalController extends Controller
{
    public function show(Request $request, string $token)
    {
        $proposal = app(OrderProposalService::class)->find($token);
        $language = $request->query('language', $proposal->order->customer_language ?: ($proposal->order->customer?->lang ?? 'it'));
        abort_unless(array_key_exists($language, app(\App\Services\CustomerLanguageService::class)->enabled()), 404);
        app()->setLocale($language);
        $choice = in_array($request->query('choice'), ['accept', 'reject'], true) ? $request->query('choice') : null;

        $languageUrls = collect(app(\App\Services\CustomerLanguageService::class)->enabled())->mapWithKeys(fn ($meta, $code) => [$code => route('public-orders.proposal', ['token' => $token, 'language' => $code, 'choice' => $choice])])->all();

        return response()->view('public-orders.proposal', compact('proposal', 'token', 'choice', 'languageUrls'))
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function respond(Request $request, string $token)
    {
        $proposal = app(OrderProposalService::class)->find($token);
        $language = $request->query('language', $proposal->order->customer_language ?: ($proposal->order->customer?->lang ?? 'it'));
        abort_unless(array_key_exists($language, app(\App\Services\CustomerLanguageService::class)->enabled()), 404);
        app()->setLocale($language);
        $request->validate(['choice' => 'required|in:accept,reject']);
        app(OrderProposalService::class)->respond($token, $request->input('choice'));

        return redirect()->route('public-orders.proposal', ['token' => $token, 'language' => $language])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }
}
