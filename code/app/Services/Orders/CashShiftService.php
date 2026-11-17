<?php

namespace App\Services\Orders;

use App\Models\CashShift;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CashShiftService
{
    public function open(int $opening): CashShift
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        Validator::make(['opening' => $opening], ['opening' => 'integer|min:0|max:100000000'])->validate();

        try {
            return DB::transaction(function () use ($opening) {
                if (CashShift::where('active_key', 'main')->exists()) {
                    throw ValidationException::withMessages(['order' => __('Un turno di cassa è già aperto.')]);
                }

                // Unique active_key also prevents concurrent openings.
                return CashShift::create(['active_key' => 'main', 'opened_by' => auth()->id(), 'opening_cents' => $opening]);
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['order' => __('Un turno di cassa è già aperto.')]);
        }
    }

    public function close(string $id, int $counted, string $notes = ''): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        Validator::make(compact('counted', 'notes'), ['counted' => 'integer|min:0|max:100000000', 'notes' => 'string|max:2000'])->validate();
        DB::transaction(function () use ($id, $counted, $notes) {
            $shift = CashShift::lockForUpdate()->findOrFail($id);
            if ($shift->closed_at) {
                return;
            }
            $expected = $shift->expectedCents();
            $shift->update(['active_key' => null, 'closed_by' => auth()->id(), 'closed_at' => now(), 'counted_cents' => $counted,
                'expected_cents' => $expected, 'difference_cents' => $counted - $expected, 'notes' => $notes]);
        });
    }
}
