<?php

return [
    'queue_connection' => env('MARKETING_QUEUE_CONNECTION', 'redis'),
    'queue' => env('MARKETING_MAIL_QUEUE', 'marketing-email'),
    'confirmation_queue' => env('MARKETING_CONFIRMATION_QUEUE', 'messages-email'),
    'timezone' => env('MARKETING_TIMEZONE', 'Europe/Rome'),
    'defaults' => ['whatsapp_max' => 4000, 'telegram_max' => 4000, 'sms_max' => 480, 'smtp_per_minute' => 20, 'smtp_per_hour' => 100, 'marketing_per_hour' => 80],
];
