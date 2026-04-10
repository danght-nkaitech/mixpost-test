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

    /*
    |--------------------------------------------------------------------------
    | Firebase Client (Web SDK) Config
    |--------------------------------------------------------------------------
    | Used by the frontend to initialize Firebase Auth.
    | Get these values from Firebase Console → Project Settings → Web App.
    */
    'firebase_client' => [
        'api_key' => env('FIREBASE_CLIENT_API_KEY'),
        'auth_domain' => env('FIREBASE_CLIENT_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_CLIENT_PROJECT_ID'),
        'app_id' => env('FIREBASE_CLIENT_APP_ID'),
    ],

];
