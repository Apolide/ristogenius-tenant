<?php

namespace App\Livewire\CashDesk;

class CustomerOrders extends \App\Livewire\CustomerOrders\Index
{
    protected function cash(): bool
    {
        return true;
    }
}
