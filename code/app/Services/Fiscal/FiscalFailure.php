<?php

namespace App\Services\Fiscal;

use RuntimeException;

class FiscalFailure extends RuntimeException
{
    public function __construct(public readonly bool $uncertain, string $message, public readonly ?int $httpStatus = null, public readonly ?string $providerCode = null)
    {
        parent::__construct($message);
    }
}
