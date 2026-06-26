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

    /*
    |--------------------------------------------------------------------------
    | EMIS API Configuration
    |--------------------------------------------------------------------------
    |
    | API EMIS Kemenag untuk validasi data NISN siswa.
    | SSL verification bisa diatur via .env untuk mengatasi masalah
    | certificate di berbagai environment.
    |
    */
    'emis' => [
        'api_url' => env('EMIS_API_URL', 'https://api-emis.kemenag.go.id/v1'),
        'bearer_token' => env('EMIS_BEARER_TOKEN'),
        'ssl_verify' => env('EMIS_SSL_VERIFY', false), // Set true di production jika tidak ada masalah SSL
    ],

    'simansa_sync' => [
        'token' => env('SIMANSA_SYNC_TOKEN'),
    ],

];
