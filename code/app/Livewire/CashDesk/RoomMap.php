<?php

namespace App\Livewire\CashDesk;

class RoomMap extends \App\Livewire\Orders\RoomMap
{
    protected function cash(): bool
    {
        return true;
    }
}
