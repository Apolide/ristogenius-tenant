<?php

namespace App\Http\Controllers;

use App\Models\RoomTable;
use App\Services\PublicOrders\TableQrService;

class OrderTableQrController extends Controller
{
    public function __invoke(RoomTable $table)
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        return response(app(TableQrService::class)->svg($table))->header('Content-Type', 'image/svg+xml')->header('Content-Disposition', 'attachment; filename="tavolo-'.$table->id.'.svg"');
    }
}
