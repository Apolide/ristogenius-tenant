<?php

return [
    'name' => env('TENANT_NAME', env('APP_NAME', 'Risto Pilot')),
    'slug' => env('TENANT_SLUG', 'tenant'),
    'id' => env('TENANT_ID'),
    'default_backend_language' => env('TENANT_DEFAULT_BACKEND_LANGUAGE', 'it'),
    'backend_languages' => [
        'it' => ['label' => 'Italiano', 'flag' => '🇮🇹'],
        'en' => ['label' => 'English', 'flag' => '🇬🇧'],
        'de' => ['label' => 'Deutsch', 'flag' => '🇩🇪'],
    ],
    // Kept for customer-facing language configuration compatibility.
    'languages' => env('TENANT_LANGUAGES', 'it,en,de'),
    // Customer-facing languages. Falls back to the legacy setting during migration.
    'customer_languages' => env('TENANT_CUSTOMER_LANGUAGES', env('TENANT_LANGUAGES', 'it,en,de')),
];
