<?php

namespace App\Console\Commands;

use App\Services\Fiscal\FiscalInvoiceService;
use Illuminate\Console\Command;

class SyncFiscalInvoices extends Command
{
    protected $signature = 'fiscal:sync-invoices {--limit=25}';

    protected $description = 'Synchronize pending sandbox invoice outcomes without re-sending documents';

    public function handle(FiscalInvoiceService $service): int
    {
        $result = $service->synchronizePending((int) $this->option('limit'));
        $this->info('Fatture sincronizzate: '.$result['synced'].'. Da verificare: '.$result['failed'].'.');

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
