## Introduction
This package is designed to simplify and automate the management of opening and closing hours for various establishments, such as stores, restaurants, gyms, or nightclubs.

## Installation and Setup
You can install the package via composer:
```bash
composer require yanselmask/openable
```
You can publish the migration with:
```bash
php artisan vendor:publish --provider="Yanselmask\Openable\Providers\ServiceProvider" --tag="openable-migrations"
```
After the migration has been published you can create the openables tables by running the migrations:
```bash
php artisan migrate
```
You can optionally publish the config file with:
```bash
php artisan vendor:publish --provider="Yanselmask\Openable\Providers\ServiceProvider" --tag="openable-config"
```
This is the contents of the published config file:

```php
<?php

return [
    /*
  |--------------------------------------------------------------------------
  | Database Name
  |--------------------------------------------------------------------------
  |
  | Here you should specify the name of the database to which your application
  | will connect. This name identifies the database on your server and is
  | necessary for the application to perform read and write operations.
  |
  | Make sure the name exactly matches the database name you have created on
  | your database server, as any discrepancy could prevent the application from
  | connecting properly.
  |
  | Example:
  | 'database_name' => 'openables',
  |
  */
    'database_name' => 'openables'
];

```