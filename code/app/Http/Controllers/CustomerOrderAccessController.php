<?php

namespace App\Http\Controllers;

use App\Models\CustomerOrderAccess;
use App\Models\DigitalMenu;
use App\Models\RoomTable;
use App\Services\PublicOrders\CustomerAccessService;
use Illuminate\Http\Request;

class CustomerOrderAccessController extends Controller
{
    public function show(Request $request, string $token)
    {
        abort_unless(strlen($token) === 64, 404);
        $access = CustomerOrderAccess::where('token_hash', hash('sha256', $token))->firstOrFail();
        $languages = array_intersect_key(app(\App\Services\CustomerLanguageService::class)->enabled(), array_flip(DigitalMenu::findOrFail($access->menu_id)->enabled_languages));
        $language = $request->query('language', $access->language);
        abort_unless(array_key_exists($language, $languages), 404);
        app()->setLocale($language);
        $languageUrls = collect($languages)->mapWithKeys(fn ($meta, $code) => [$code => route('public-orders.verify', ['token' => $token, 'language' => $code])])->all();
        abort_if($access->consumed_at || $access->expires_at->isPast(), 410, __('Link scaduto o già utilizzato.'));

        return response()->view('public-orders.verify', compact('token', 'languageUrls'))->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function redeem(Request $request, string $token)
    {
        return redirect(app(CustomerAccessService::class)->redeem($token))->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function table(string $language, string $tableToken)
    {
        abort_unless(array_key_exists($language, app(\App\Services\CustomerLanguageService::class)->enabled()), 404);
        app()->setLocale($language);
        RoomTable::where('ordering_token', $tableToken)->whereHas('room', fn ($q) => $q->where('active', true))->firstOrFail();
        $menus = DigitalMenu::where('is_published', true)->where('is_visible', true)->orderBy('position')->get()->filter(fn ($m) => in_array($language, $m->enabled_languages, true));
        if ($menus->count() === 1) {
            return redirect()->route('public-orders.menu', ['language' => $language, 'menu' => $menus->first(), 'table' => $tableToken]);
        }

        return view('public-orders.choose-menu', compact('menus', 'language', 'tableToken'));
    }
}
