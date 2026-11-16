<?php

namespace App\Http\Controllers;

use App\Models\CustomerOrderAccess;
use App\Models\DigitalMenu;
use App\Models\RoomTable;
use App\Services\PublicOrders\CustomerAccessService;
use Illuminate\Http\Request;

class CustomerOrderAccessController extends Controller
{
    public function show(string $token)
    {
        abort_unless(strlen($token) === 64, 404);
        $access = CustomerOrderAccess::where('token_hash', hash('sha256', $token))->firstOrFail();
        abort_if($access->consumed_at || $access->expires_at->isPast(), 410, 'Link scaduto o già utilizzato.');

        return response()->view('public-orders.verify', compact('token'))->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function redeem(Request $request, string $token)
    {
        return redirect(app(CustomerAccessService::class)->redeem($token))->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function table(string $language, string $tableToken)
    {
        RoomTable::where('ordering_token', $tableToken)->whereHas('room', fn ($q) => $q->where('active', true))->firstOrFail();
        $menus = DigitalMenu::where('is_published', true)->where('is_visible', true)->orderBy('position')->get()->filter(fn ($m) => in_array($language, $m->enabled_languages, true));
        if ($menus->count() === 1) {
            return redirect()->route('public-orders.menu', ['language' => $language, 'menu' => $menus->first(), 'table' => $tableToken]);
        }

        return view('public-orders.choose-menu', compact('menus', 'language', 'tableToken'));
    }
}
