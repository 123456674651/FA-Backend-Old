<?php

return [

    'libreoffice' => [
        // Used for agreement PDF export and for converting legacy .doc
        // templates to .docx on upload. The Windows fallback matches
        // config/doxswap.php so a dev machine works without extra .env setup.
        'binary' => env('LIBREOFFICE_BINARY', PHP_OS_FAMILY === 'Windows'
            ? 'C:\Program Files\LibreOffice\program\soffice.exe'
            : '/usr/bin/libreoffice'),
    ],

    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    ],

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

    // Used by Api\V2\AuthController's mobile OTP login/verify.
    // When `template_id` is set, OTPs go through MSG91's plain Template API.
    // Until then (template not yet DLT-approved), it falls back to the
    // Widget API using `widget_id`.
    'msg91' => [
        'auth_key' => env('MSG91_AUTH_KEY'),
        'template_id' => env('MSG91_OTP_TEMPLATE_ID'),
        'widget_id' => env('MSG91_WIDGET_ID'),
    ],

];
