<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET'),
    ],

    'suppliers' => [
        'url' => env('SUPPLIERS_URL'),
        'api_url' => env('SUPPLIERS_API_URL'),
        'system_token' => env('SUPPLIERS_API_SYSTEM_TOKEN')
    ],

    'meta' => [
        'app_id' => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
        'api_version' => env('META_API_VERSION'),
        'graph_baseurl' => env('META_GRAPH_BASEURL'),
        'api_token' => env('META_GRAPH_API_TOKEN'),
        'verify_token' => env('META_WEBHOOK_VERIFY_TOKEN'),
        'private_key_pass' => env('META_PRIVATE_KEY_PASS'),
        'whatsapp_number_id' => env('META_WHATSAPP_NUMBER_ID'),
        'owner_waba_id' => env('META_OWNER_WABA_ID'),
        'meta_flow_booking_flowid' => env('META_FLOW_BOOKING_FLOWID'),
        'login' =>[
            'configuration_id' => env('META_LOGIN_CONFIGURATION_ID')
        ],
        
    ],

    'skebby' => [
        'username' => env('SKEBBY_USERNAME'),
        'password' => env('SKEBBY_PASSWORD'),
        'base_url' => env('SKEBBY_BASEURL', 'https://api.skebby.it/API/v1.0/REST/'),
    ]

];
