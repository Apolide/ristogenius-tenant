<?php

namespace App\Contracts\Fiscal;

interface FiscalProvider
{
    public function assertAvailable(): void;

    /** Provider-independent commands and normalized results. Never retries writes. */
    public function execute(string $action, array $data, ?string $operationId = null): array;
}
