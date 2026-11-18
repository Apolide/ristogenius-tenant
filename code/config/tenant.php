<?php

return [
    'name' => env('TENANT_NAME', env('APP_NAME', 'Risto Pilot')),
    'slug' => env('TENANT_SLUG', 'tenant'),
    'id' => env('TENANT_ID'),
    'default_backend_language' => env('TENANT_DEFAULT_BACKEND_LANGUAGE', 'it'),
    'backend_languages' => env('TENANT_BACKEND_LANGUAGES', 'it,en,de'),
    'customer_languages' => env('TENANT_CUSTOMER_LANGUAGES', 'it,en,de'),
];
