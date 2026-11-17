<?php

namespace App\Services\Orders;

use App\Models\OrderCourse;
use Illuminate\Validation\ValidationException;

class ProductionService
{
    public function send(OrderCourse $course): void
    {
        $this->check(! $course->rejected_at, __('Uscita rifiutata.'));
        if ($course->sent_at) {
            return;
        }
        $this->check($course->items->isNotEmpty(), __('L’uscita è vuota.'));
        foreach ($course->items->where('requires_production', true)->groupBy('department_id') as $department => $items) {
            $this->check($department !== '' && \App\Models\Department::whereKey($department)->where('production', true)->exists(), __('Reparto produttivo non più disponibile.'));
            $ticket = $course->tickets()->create(['department_id' => $department, 'department_name' => $items->first()->department_name, 'sent_at' => now()]);
            $course->items()->whereIn('id', $items->pluck('id'))->update(['production_ticket_id' => $ticket->id]);
        }
        $course->update(['sent_at' => now()]);

    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['order' => $message]);
        }
    }
}
