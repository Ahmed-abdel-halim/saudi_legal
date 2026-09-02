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

    'stripe' => [
        'key'               => env('STRIPE_KEY'),
        'secret'            => env('STRIPE_SECRET'),
        'webhook_secret'    => env('STRIPE_WEBHOOK_SECRET'),
        'webhook_secret_ai' => env('STRIPE_WEBHOOK_SECRET_AI'),
    ],

    'gemini' => [
        'key'    => env('GEMINI_API_KEY'),
        'models' => env('GEMINI_MODELS', ''),
    ],

    'bedrock' => [
        'enabled' => env('BEDROCK_ENABLED', false),
        'key'     => env('AWS_BEDROCK_ACCESS_KEY_ID', env('AWS_ACCESS_KEY_ID', '')),
        'secret'  => env('AWS_BEDROCK_SECRET_ACCESS_KEY', env('AWS_SECRET_ACCESS_KEY', '')),
        'region'  => env('AWS_BEDROCK_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
        'model'   => env('AWS_BEDROCK_MODEL', 'anthropic.claude-3-5-sonnet-20240620-v1:0'),
    ],

    'qdrant' => [
        'url'        => env('QDRANT_URL', ''),
        'api_key'    => env('QDRANT_API_KEY', ''),
        'collection' => env('QDRANT_COLLECTION', 'legal-documents'),
        'enabled'    => env('QDRANT_ENABLED', false),
    ],

    'twilio' => [
        'sid'           => env('TWILIO_SID', ''),
        'token'         => env('TWILIO_AUTH_TOKEN', ''),
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM', ''),
        'free_limit'    => env('WHATSAPP_FREE_LIMIT', 10),
        // Content Template SIDs — يتم ملؤها بعد تشغيل: php artisan whatsapp:create-templates
        'templates'     => [
            'main_menu'    => env('TWILIO_TEMPLATE_MAIN_MENU', ''),
            'in_chat'      => env('TWILIO_TEMPLATE_IN_CHAT', ''),
            'ended_chat'   => env('TWILIO_TEMPLATE_ENDED_CHAT', ''),
            'after_plans'  => env('TWILIO_TEMPLATE_AFTER_PLANS', ''),
        ],
    ],

    'chatwoot' => [
        'url'        => env('CHATWOOT_URL', 'https://app.chatwoot.com'),
        'account_id' => env('CHATWOOT_ACCOUNT_ID', '177354'),
        'token'      => env('CHATWOOT_API_TOKEN', 'NVvdPMa9b2VVA5tFMsiYbz1B'),
        'inbox_id'   => env('CHATWOOT_INBOX_ID', '124151'),
    ],

];
