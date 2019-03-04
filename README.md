# Upinde

TODO


## Requirements

- PHP 7.0


## Installation

```
composer install wizaplace/upinde
```


## Usage

```php
<?php

// From your database
$from = [
    new RandomObject("foo", 10.5),
    new RandomObject("bar", 9.5),
    new RandomObject("barFoo", 9.5),
    new RandomObject("barFooBar", 9.5),
];

// From you form like $_POST
$to = [
    [
        "name" => "foo",
        "price" => 11.5
    ],
    [
        "name" => "bar",
        "price" => 9.5,
    ],
    [
        "name" => "fooBar",
        "price" => 8.5,
    ],
];

$upinde = new \Wizaplace\Upinde\Upinde('name', $from, $to);

$upinde->getQueries();

// Or
$upinde->getNothing();
$upinde->getInsert();
$upinde->getUpdate();
$upinde->getDelete();
```
