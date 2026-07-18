<?php

return [
    'transport' => env('MESSAGE_TRANSPORT', 'redis'),
    'queue' => [
        'connection' => env('MESSAGE_QUEUE_CONNECTION', 'redis'),
        'dispatch' => env('MESSAGE_DISPATCH_QUEUE', 'messages-dispatch'),
        'channels' => [
            'email' => env('MESSAGE_EMAIL_QUEUE', 'messages-email'),
            'telegram' => env('MESSAGE_TELEGRAM_QUEUE', 'messages-telegram'),
            'whatsapp' => env('MESSAGE_WHATSAPP_QUEUE', 'messages-whatsapp'),
            'sms' => env('MESSAGE_SMS_QUEUE', 'messages-sms'),
        ],
    ],
    'outbox' => [
        'batch_size' => (int) env('MESSAGE_OUTBOX_BATCH_SIZE', 100),
        'retry_seconds' => (int) env('MESSAGE_OUTBOX_RETRY_SECONDS', 60),
        'processing_timeout_minutes' => (int) env('MESSAGE_OUTBOX_PROCESSING_TIMEOUT', 10),
    ],
];
