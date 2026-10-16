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

    'papi' => [
        'api_key' => env('PAPI_API_KEY'),
        'url' => env('PAPI_URL', 'https://app.papi.mg/engine/api'),
        'test_mode' => env('PAPI_TEST_MODE', true),
        'valid_duration' => env('PAPI_VALID_DURATION', 60),

        'success_url' => env('PAPI_SUCCESS_URL'),
        'failure_url' => env('PAPI_FAILURE_URL'),
        'notification_url' => env('PAPI_NOTIFICATION_URL'),
        'webhook_secret' => env('PAPI_WEBHOOK_SECRET'),
    ],

];
