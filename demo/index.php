<?php
/**
 * @author      Wizaplace DevTeam <dev@wizaplace.com>
 * @copyright   Copyright (c) Wizaplace
 * @license     MIT
 */

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Tracy\Debugger;
use Wizaplace\CollectionDiff\CollectionDiff;
use Wizaplace\Test\CollectionDiff\RandomObject;

// Enable Tracy
Debugger::enable(Debugger::DEVELOPMENT);
Debugger::$strictMode = true;
Debugger::$maxDepth = 15;
Debugger::$maxLength = 250;

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

$collectionDiff = new CollectionDiff(['name', 'category'], $from, $to);
dump($collectionDiff->getQueries());
