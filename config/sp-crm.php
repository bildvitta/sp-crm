<?php

return [
    'table_prefix' => env('MS_SP_CRM_TABLE_PREFIX', 'crm_'),

    'model_user' => '\App\Models\User',

    'db' => [
        'host' => env('CRM_DB_HOST', '127.0.0.1'),
        'port' => env('CRM_DB_PORT', '3306'),
        'database' => env('CRM_DB_DATABASE', 'forge'),
        'username' => env('CRM_DB_USERNAME', 'forge'),
        'password' => env('CRM_DB_PASSWORD', ''),
    ],

    'rabbitmq' => [
        'host' => env('RABBITMQ_HOST'),
        'port' => env('RABBITMQ_PORT'),
        'user' => env('RABBITMQ_USER'),
        'password' => env('RABBITMQ_PASSWORD'),
        'virtualhost' => env('RABBITMQ_VIRTUALHOST', '/'),
        'exchange' => [
            'customers' => env('RABBITMQ_EXCHANGE_CUSTOMERS', 'customers'),
        ],
        'queue' => [
            'customers' => env('RABBITMQ_QUEUE_CUSTOMERS'),
        ]
    ],
];
