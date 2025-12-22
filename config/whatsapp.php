<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for WhatsApp Business API integration.
    | You can enable/disable the service and configure API credentials here.
    |
    */

    'enabled' => env('WHATSAPP_API_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Test Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, messages will be simulated instead of actually sent.
    | Useful for development and testing without WhatsApp API credentials.
    |
    */

    'test_mode' => env('WHATSAPP_TEST_MODE', true),

    'api' => [
        'version' => env('WHATSAPP_API_VERSION', 'v16.0'),
        'base_url' => 'https://graph.facebook.com',
        'timeout' => 30,
    ],

    'credentials' => [
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'webhook_secret' => env('WHATSAPP_WEBHOOK_SECRET'),
    ],

    'webhook' => [
        'callback_url' => env('WHATSAPP_CALLBACK_URL'),
        'verify_token' => env('WHATSAPP_WEBHOOK_SECRET'),
    ],

    'templates' => [
        'namespace' => env('WHATSAPP_TEMPLATE_NAMESPACE'),
        
        // Default message templates for pharmacy
        'defaults' => [
            'prescription_ready' => [
                'name' => 'prescription_ready',
                'language' => 'en',
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => '{{customer_name}}'],
                            ['type' => 'text', 'text' => '{{prescription_id}}'],
                            ['type' => 'text', 'text' => '{{pharmacy_name}}'],
                        ]
                    ]
                ]
            ],
            'appointment_reminder' => [
                'name' => 'appointment_reminder',
                'language' => 'en',
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => '{{customer_name}}'],
                            ['type' => 'text', 'text' => '{{appointment_date}}'],
                            ['type' => 'text', 'text' => '{{appointment_time}}'],
                        ]
                    ]
                ]
            ],
            'medication_reminder' => [
                'name' => 'medication_reminder',
                'language' => 'en',
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => '{{customer_name}}'],
                            ['type' => 'text', 'text' => '{{medication_name}}'],
                            ['type' => 'text', 'text' => '{{dosage}}'],
                        ]
                    ]
                ]
            ],
            'promotional_message' => [
                'name' => 'promotional_message',
                'language' => 'en',
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => '{{customer_name}}'],
                            ['type' => 'text', 'text' => '{{offer_details}}'],
                            ['type' => 'text', 'text' => '{{pharmacy_name}}'],
                        ]
                    ]
                ]
            ]
        ]
    ],

    'rate_limits' => [
        'messages_per_minute' => 100,
        'messages_per_hour' => 1000,
        'messages_per_day' => 10000,
    ],

    'logging' => [
        'enabled' => true,
        'channel' => 'whatsapp',
        'level' => 'info',
    ],

    'features' => [
        'bulk_messaging' => true,
        'message_templates' => true,
        'delivery_reports' => true,
        'read_receipts' => true,
        'media_messages' => true,
    ],
];
