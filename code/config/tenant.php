<?php

return [
    'name' => env('TENANT_NAME', env('APP_NAME', 'Risto Pilot')),
    'slug' => env('TENANT_SLUG', 'tenant'),
    'id' => env('TENANT_ID'),
    'languages' => env('TENANT_LANGUAGES', 'it,en'),
    // Customer-facing languages. Falls back to the legacy setting during migration.
    'customer_languages' => env('TENANT_CUSTOMER_LANGUAGES', env('TENANT_LANGUAGES', 'it,en')),
];
