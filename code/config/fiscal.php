<?php

return [
    'provider' => 'openapi',
    // E-Receipts is currently documented as TEST-only. No production switch.
    'environment' => 'sandbox',
    'openapi' => [
        'token' => env('OPENAPI_INVOICE_SANDBOX_TOKEN', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'),
        'timeout' => 30,
        'connect_timeout' => 5,
    ],
    // Per-request estimates in micro-euro, keyed by action. Populate only from
    // the tenant's contracted price list. Sandbox requests are always zero.
    'tariffs_micros' => [],
];
