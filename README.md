[![Latest Version on Packagist](https://img.shields.io/packagist/v/bildvitta/sp-produto.svg?style=flat-square)](https://packagist.org/packages/bildvitta/sp-produto)
[![Total Downloads](https://img.shields.io/packagist/dt/bildvitta/sp-produto.svg?style=flat-square)](https://packagist.org/packages/bildvitta/sp-produto)

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

With the configuration file sp-crm.php published in your configuration folder it is necessary to create environment variables in your .env file:

```
MS_SP_CRM_TABLE_PREFIX="crm_"
```
