<?php

namespace App\Console\Commands;

use App\Models\CustomerOrderAccess;
use App\Models\CustomerOrderSession;
use Illuminate\Console\Command;

class PruneCustomerOrderAccess extends Command
{
    protected $signature = 'customer-orders:prune-access';

    protected $description = 'Remove expired public ordering sessions and old access requests';

    public function handle(): int
    {
        CustomerOrderSession::where('expires_at', '<', now()->subDay())->delete();
        CustomerOrderAccess::where('expires_at', '<', now()->subDays(7))->delete();

        return self::SUCCESS;
    }
}
