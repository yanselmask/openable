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

# Usage
### Add openable functionality to your resource model

To add openable functionality to your resource model just use the `\Yanselmask\Openable\Traits\Openable` trait like this:

```php
namespace App\Models;

use Yanselmask\Openable\Traits\Openable;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use Openable;
}
```

A set of opening hours is created by passing in a regular schedule, and a list of exceptions
```php
// Add the use at the top of each file where you want to use the OpeningHours class:
use App\Models\Restaurant;


$restaurant = Restaurant::find(1);
$restaurant->setShift(
[
    'monday'     => ['09:00-12:00', '13:00-18:00'],
    'tuesday'    => ['09:00-12:00', '13:00-18:00'],
    'wednesday'  => ['09:00-12:00'],
    'thursday'   => ['09:00-12:00', '13:00-18:00'],
    'friday'     => ['09:00-12:00', '13:00-20:00'],
    'saturday'   => ['09:00-12:00', '13:00-16:00'],
    'sunday'     => [],
    'exceptions' => [
        '2016-11-11' => ['09:00-12:00'],
        '2016-12-25' => [],
        '01-01'      => [],                // Recurring on each 1st of January
        '12-25'      => ['09:00-12:00'],   // Recurring on each 25th of December
    ]
]
);
```
The object can be queried for a day in the week, which will return a result based on the regular schedule
```php
// Open on Mondays:
$restaurant->isOpenOn('monday'); // true

// Closed on Sundays:
$restaurant->isOpenOn('sunday'); // false
```
It can also be queried for a specific date and time:
```php
// Closed because it's after hours:
$restaurant->isOpenAt(new DateTime('2016-09-26 19:00:00')); // false

// Closed because Christmas was set as an exception
$restaurant->isOpenOn('2016-12-25'); // false

// Checks if the business is closed on a specific day, at a specific time.
$restaurant->isClosedAt(new DateTime('2016-26-09 20:00'));
// Checks if the business is closed on a day in the regular schedule.
$restaurant->isClosedOn('sunday');
```
It can also return arrays of opening hours for a week or a day:
```php
// OpeningHoursForDay object for the regular schedule
$restaurant->forDay('monday');

// OpeningHoursForDay[] for the regular schedule, keyed by day name
$restaurant->forWeek();

// Array of day with same schedule for the regular schedule, keyed by day name, days combined by working hours
$restaurant->forWeekCombined();

// OpeningHoursForDay object for a specific day
$restaurant->forDate(new DateTime('2016-12-25'));

// OpeningHoursForDay[] of all exceptions, keyed by date
$restaurant->exceptions();
```
On construction, you can set a flag for overflowing times across days. For example, for a nightclub opens until 3am on Friday and Saturday:
```php
use App\Models\Nightclub;

$nightclub = Nightclub::find(1);

$nightclub->setShift([
    'overflow' => true,
    'friday'   => ['20:00-03:00'],
    'saturday' => ['20:00-03:00'],
]);
```
This allows the API to further at previous day's data to check if the opening hours are open from its time range.

You can add data in definitions then retrieve them:
```php
$restaurant->setShift([
'monday' => [
        'data' => 'Typical Monday',
        '09:00-12:00',
        '13:00-18:00',
    ],
    'tuesday' => [
        '09:00-12:00',
        '13:00-18:00',
        [
            '19:00-21:00',
            'data' => 'Extra on Tuesday evening',
        ],
    ],
    'exceptions' => [
        '2016-12-25' => [
            'data' => 'Closed for Christmas',
        ],
    ],
]);
echo $restaurant->forDay('monday')->data; // Typical Monday
echo $restaurant->forDate(new DateTime('2016-12-25'))->data; // Closed for Christmas
echo $restaurant->forDay('tuesday')[2]->data; // Extra on Tuesday evening
```
In the example above, data are strings but it can be any kind of value. So you can embed multiple properties in an array.

For structure convenience, the data-hours couple can be a fully-associative array, so the example above is strictly equivalent to the following:
```php
$restaurant->setShift([
    'monday' => [
        'hours' => [
            '09:00-12:00',
            '13:00-18:00',
        ],
        'data' => 'Typical Monday',
    ],
    'tuesday' => [
        ['hours' => '09:00-12:00'],
        ['hours' => '13:00-18:00'],
        ['hours' => '19:00-21:00', 'data' => 'Extra on Tuesday evening'],
    ],
    // Open by night from Wednesday 22h to Thursday 7h:
    'wednesday' => ['22:00-24:00'], // use the special "24:00" to reach midnight included
    'thursday' => ['00:00-07:00'],
    'exceptions' => [
        '2016-12-25' => [
            'hours' => [],
            'data'  => 'Closed for Christmas',
        ],
    ],
]);
```
You can use the separator `to` to specify multiple days at once, for the week or for exceptions:
```php
$restaurant->setShift([
    'monday to friday' => ['09:00-19:00'],
    'saturday to sunday' => [],
    'exceptions' => [
        // Every year
        '12-24 to 12-26' => [
            'hours' => [],
            'data'  => 'Holidays',
        ],
        // Only happening in 2024
        '2024-06-25 to 2024-07-01' => [
            'hours' => [],
            'data'  => 'Closed for works',
        ],
    ],
]);
```
The last structure tool is the filter, it allows you to pass closures (or callable function/method reference) that take a date as a parameter and returns the settings for the given date.
```php
$restaurant->setShift([
    'monday' => [
       '09:00-12:00',
    ],
    'filters' => [
        function ($date) {
            $year         = intval($date->format('Y'));
            $easterMonday = new DateTimeImmutable('2018-03-21 +'.(easter_days($year) + 1).'days');
            if ($date->format('m-d') === $easterMonday->format('m-d')) {
                return []; // Closed on Easter Monday
                // Any valid exception-array can be returned here (range of hours, with or without data)
            }
            // Else the filter does not apply to the given date
        },
    ],
]);
```
If a callable is found in the `"exceptions"` property, it will be added automatically to filters so you can mix filters and exceptions both in the exceptions array. The first filter that returns a non-null value will have precedence over the next filters and the filters array has precedence over the filters inside the exceptions array.

Warning: We will loop on all filters for each date from which we need to retrieve opening hours and can neither predicate nor cache the result (can be a random function) so you must be careful with filters, too many filters or long process inside filters can have a significant impact on the performance.

It can also return the next open or close `DateTime` from a given `DateTime`.
```php
// The next open datetime is tomorrow morning, because we’re closed on 25th of December.
$nextOpen = $restaurant->nextOpen(new DateTime('2016-12-25 10:00:00')); // 2016-12-26 09:00:00

// The next open datetime is this afternoon, after the lunch break.
$nextOpen = $restaurant->nextOpen(new DateTime('2016-12-24 11:00:00')); // 2016-12-24 13:00:00


// The next close datetime is at noon.
$nextClose = $restaurant->nextClose(new DateTime('2016-12-24 10:00:00')); // 2016-12-24 12:00:00

// The next close datetime is tomorrow at noon, because we’re closed on 25th of December.
$nextClose = $restaurant->nextClose(new DateTime('2016-12-25 15:00:00')); // 2016-12-26 12:00:00
```
If no timezone is specified, `Openable` will just assume you always pass `DateTime` objects that have already the timezone matching your schedule.

If you pass a `$timezone` as a second argument or via the array-key `'timezone'` (it can be either a `DateTimeZone` object or a `string`), then passed dates will be converted to this timezone at the beginning of each method, then if the method return a date object (such as `nextOpen`, `nextClose`, `previousOpen`, `previousClose`, `currentOpenRangeStart` or `currentOpenRangeEnd`), then it's converted back to original timezone before output so the object can reflect a moment in user local time while `OpeningHours` can stick in its own business timezone.

Alternatively you can also specify both input and output timezone (using second and third argument) or using an array:
```php
$restaurant->setShift([
    'monday' => ['09:00-12:00', '13:00-18:00'],
    'timezone' => [
        'input' => 'America/New_York',
        'output' => 'Europe/Oslo',
    ],
]);
```
For safety sake, creating `OpeningHours` object with overlapping ranges will throw an exception unless you pass explicitly `'overflow' => true`, in the opening hours array definition. You can also explicitly merge them.
```php
$ranges = [
  'monday' => ['08:00-11:00', '10:00-12:00'],
];
$mergedRanges = $restaurant->op()->mergeOverlappingRanges($ranges); // Monday becomes ['08:00-12:00']

$restaurant->setShift($mergedRanges);
// Or use the following shortcut to create from ranges that possibly overlap:
$restaurant->op()->createAndMergeOverlappingRanges($ranges);
```
Checks if the business is open right now.
```php
$restaurant->isOpen();
```
Checks if the business is closed right now.
```php
$restaurant->isClosed();
```
## Security
If you've found a bug regarding security please mail [info@yanselmask.com](info@yanselmask.com) instead of using the issue tracker.
## Credits
- [Spatie Opening Hours](https://github.com/spatie/opening-hours)
- [Yanselmask](https://yanselmask.com/)

[![Buymeacoffe](bmc-button.png)](https://buymeacoffee.com/yanselmask)