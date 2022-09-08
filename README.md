[![Latest Version on Packagist](https://img.shields.io/packagist/v/bildvitta/sp-crm.svg?style=flat-square)](https://packagist.org/packages/bildvitta/sp-crm)
[![Total Downloads](https://img.shields.io/packagist/dt/bildvitta/sp-crm.svg?style=flat-square)](https://packagist.org/packages/bildvitta/sp-crm)

## Introduction

The SP (Space Probe) package is responsible for collecting remote data updates for the module, keeping the data structure similar as possible, through the message broker.

## Installation

You can install the package via composer:

```bash 
composer require bildvitta/sp-crm:dev-develop
```

For everything to work perfectly in addition to having the settings file published in your application, run the command below:

```bash
php artisan sp:install
```

## Configuration

This is the contents of the published config file:

```php
return [
    'table_prefix' => env('MS_SP_CRM_TABLE_PREFIX', 'crm_'),
    'db' => [
        'host' => env('CRM_DB_HOST', '127.0.0.1'),
        'port' => env('CRM_DB_PORT', '3306'),
        'database' => env('CRM_DB_DATABASE', 'forge'),
        'username' => env('CRM_DB_USERNAME', 'forge'),
        'password' => env('CRM_DB_PASSWORD', ''),
    ],
    'rabbitmq' => [
        'host' => env('RABBITMQ_HOST'),
        'port' => env('RABBITMQ_PORT', '5672'),
        'user' => env('RABBITMQ_USER'),
        'password' => env('RABBITMQ_PASSWORD'),
        'virtualhost' => env('RABBITMQ_VIRTUALHOST', '/'),
        'exchange' => [],
        'queue' => []
    ],
];
```

## Importing data

You can import initial data from the parent module by setting the database connection data in the configuration file. However, it will be necessary to import the data from the dependent module first: sp-hub.

```bash
php artisan dataimport:crm_customers
```

## Database seeder

You can seed your database with fake data to work with. However, it will be necessary to seed the other dependency first: sp-hub.

```bash
php artisan db:seed --class=SpCrmSeeder
```

## Running the worker

After setting the message broker access data in the configuration file, you can run the worker to keep the data up to date.

```bash
php artisan rabbitmqworker:customers
```
