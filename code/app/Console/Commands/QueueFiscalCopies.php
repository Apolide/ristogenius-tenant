<?php

namespace App\Console\Commands;

use App\Services\Fiscal\FiscalCopyService;
use Illuminate\Console\Command;

class QueueFiscalCopies extends Command
{
    protected $signature = 'fiscal:queue-copies';

    protected $description = 'Queue requested sandbox fiscal copies after confirmed issuance';

    public function handle(FiscalCopyService $service): int
    {
        $this->info('Copie email accodate: '.$service->queueReady());

        return self::SUCCESS;
    }
}
