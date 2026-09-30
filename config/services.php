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

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY'),
        'secret_key' => env('PAYSTACK_SECRET_KEY'),
        'payment_url' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
        'merchant_email' => env('MERCHANT_EMAIL'),
        'platform_fee_percentage' => env('PAYSTACK_PLATFORM_FEE_PERCENTAGE', 7),
    ],

    'google_maps' => [
        'map_id' => env('GOOGLE_MAPS_ID'),
        'map_key' => env('GOOGLE_MAPS_KEY'),
    ],

    'hubtel' => [
        'base_url' => env('HUBTEL_BASE_URL', 'https://sms.hubtel.com'),
        'client_id' => env('HUBTEL_CLIENT_ID'),
        'client_secret' => env('HUBTEL_CLIENT_SECRET'),
        'sender_id' => env('HUBTEL_SENDER_ID', 'Suptix'),
    ],

    'giant_sms' => [
        'base_url' => env('GIANT_SMS_BASE_URL', 'https://api.giantsms.com/api/v1'),
        'username' => env('GIANT_SMS_USERNAME'),
        'password' => env('GIANT_SMS_PASSWORD'),
        'sender_id' => env('GIANT_SMS_SENDER_ID', 'Suptix'),
    ],

    'sms' => [
        // Set to 'giant_sms' to make GiantSMS the primary provider.
        'primary' => env('SMS_PRIMARY_PROVIDER', 'hubtel'),
    ],
];
