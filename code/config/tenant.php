<?php

return [
    'name' => env('TENANT_NAME', env('APP_NAME', 'Risto Pilot')),
    'slug' => env('TENANT_SLUG', 'tenant'),
    'id' => env('TENANT_ID'),
    'languages' => env('TENANT_LANGUAGES', 'it,en'),
];
