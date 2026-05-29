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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'bakong' => [
        'base_url' => env('BAKONG_BASE_URL', 'https://api-bakong.nbc.org.kh'),
        'token' => env('BAKONG_TOKEN'),
        'account_id' => env('BAKONG_ACCOUNT_ID'),
        'merchant_id' => env('BAKONG_MERCHANT_ID', '123456'),
        'merchant_name' => env('BAKONG_MERCHANT_NAME', 'DAVIT YEM'),
        'merchant_city' => env('BAKONG_MERCHANT_CITY', 'Phnom Penh'),
        'acquiring_bank' => env('BAKONG_ACQUIRING_BANK', 'Dev Bank'),
        'merchant_category_code' => env('BAKONG_MCC', '5999'),
        'country_code' => env('BAKONG_COUNTRY_CODE', 'KH'),
        'currency' => env('BAKONG_CURRENCY', 'USD'),
        'qr_timeout_minutes' => env('BAKONG_QR_TIMEOUT_MINUTES', 10),
    ],

];
