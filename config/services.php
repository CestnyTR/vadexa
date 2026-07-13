<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'netgsm' => [
        'mock' => env('NETGSM_MOCK', true),
        'url' => env('NETGSM_API_URL', 'https://api.netgsm.com.tr/sms/send/get/'),
        'user_code' => env('NETGSM_USER_CODE'),
        'password' => env('NETGSM_PASSWORD'),
        'header' => env('NETGSM_HEADER'),
    ],

    'notifications' => [
        'enable_sms' => env('NOTIFICATIONS_ENABLE_SMS', true),
        'enable_mail' => env('NOTIFICATIONS_ENABLE_MAIL', true),
        'mock_mail' => env('NOTIFICATIONS_MOCK_MAIL', true),
        'schedule_time' => env('NOTIFICATIONS_SCHEDULE_TIME', '10:00'),
    ],

    'iyzico' => [
        // When mock is true (default), payments are simulated end-to-end and no
        // real API call is made. Set IYZICO_MOCK=false and fill the keys below
        // to go live — that is the only remaining step.
        'mock' => env('IYZICO_MOCK', true),
        'base_url' => env('IYZICO_BASE_URL', 'https://sandbox-api.iyzipay.com'),
        'api_key' => env('IYZICO_API_KEY'),
        'secret_key' => env('IYZICO_SECRET_KEY'),
        'currency' => env('IYZICO_CURRENCY', 'TRY'),
    ],

];
