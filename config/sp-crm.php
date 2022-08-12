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
        'host' => env('RABBITMQ_HOST', 'b-5e375e25-4b8a-4777-a362-388150b78d9a.mq.us-east-1.amazonaws.com'),
        'port' => env('RABBITMQ_PORT', '5671'),
        'user' => env('RABBITMQ_USER', 'rabbittest'),
        'password' => env('RABBITMQ_PASSWORD', 'runkat-nyqred-3gyTxi'),
        'virtualhost' => env('RABBITMQ_VIRTUALHOST', '/'),
        'exchange' => [
            'customers' => env('RABBITMQ_EXCHANGE_CUSTOMERS', 'customers'),
        ],
        'queue' => [
            'customers' => env('RABBITMQ_QUEUE_CUSTOMERS', 'customers.vendas'),
        ]
    ],
];
