# Collection Diff

Collection Diff allows you to compare 2 arrays (of arrays or objects) to see if you should make INSERT, UPDATE or DELETE
in your SQL tables.


## Requirements

- PHP 7.1


## Installation

```
composer install wizaplace/collection-diff
```


## Usage

```php
<?php

// From your database
$from = [
    new RandomObject("foo", "category1", 10.5),
    new RandomObject("bar2", "category2", 9.5),
    new RandomObject("bar1", "category1", 9.5),
    new RandomObject("barFoo", "category1", 9.5),
    new RandomObject("barFooBar", "category1", 9.5),
];

// From you form like $_POST
$to = [
    [
        "name" => "foo",
        "category" => "category1",
        "price" => 11.5
    ],
    [
        "name" => "bar2",
        "category" => "category2",
        "price" => 9.5,
    ],
    [
        "name" => "bar1",
        "category" => "category2",
        "price" => 9.5,
    ],
    [
        "name" => "fooBar",
        "category" => "category3",
        "price" => 8.5,
    ],
];

$collectionDiff = new \Wizaplace\CollectionDiff\CollectionDiff(['name', 'category'], $from, $to);

$collectionDiff->getQueries();

// Or
$collectionDiff->getNothing();
$collectionDiff->getInsert();
$collectionDiff->getUpdate();
$collectionDiff->getDelete();
```
