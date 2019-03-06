# Collection Diff

Collection Diff allows you to compare 2 data collections to find operations to do: CREATE, UPDATE or DELETE data.

## Requirements

- PHP 7.1

## Installation

```
composer install wizaplace/collection-diff
```

## Usage

If you wan to compare data between database and form (like $_POST) 

```php
<?php

// From your database
$from = [
    new FooObject("foo", "category1", 10.5),
    new FooObject("bar2", "category2", 9.5),
    new FooObject("bar1", "category1", 9.5),
    new FooObject("barFoo", "category1", 9.5),
    new FooObject("barFooBar", "category1", 9.5),
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
```

You need to call `CollectionDiff::compare` to execute the comparison then call `CollectionDiff::getActions` to retrive
actions.

`CollectionDiff::compare` first parameter is an array of primary keys needed by Collection Diff to execute the
comparison to check the concordance on this keys.

Your object will be normalized to be compared.

See example above.

### Symfony

If you want to use Collection Diff in your Symfony project, you can use the bridge 
`Wizaplace\CollectionDiff\Bridge\CollectionDiffBundle`.

```php
$container
    ->get(\Wizaplace\CollectionDiff\CollectionDiff::class)
    ->compare(['name', 'category'], $from, $to, false, true)
    ->getActions();
```

### Standalone

```php
$mySerializer = new FooSerializer();

$collectionDiff = new Wizaplace\CollectionDiff\CollectionDiff($mySerializer);
$collectionDiff->compare(['name', 'category'], $from, $to, false, true);

$collectionDiff->getActions();

// Or
$collectionDiff->getNothing();
$collectionDiff->getCreate();
$collectionDiff->getUpdate();
$collectionDiff->getDelete();
``` 

### Results

```
array (size=4)
  1 => 
    array (size=1)
      0 => 
        array (size=3)
          'name' => string 'bar2' (length=4)
          'category' => string 'category2' (length=9)
          'price' => float 9.5
  2 => 
    array (size=1)
      0 => 
        array (size=3)
          'name' => string 'fooBar' (length=6)
          'category' => string 'category3' (length=9)
          'price' => float 8.5
  3 => 
    array (size=2)
      0 => 
        array (size=3)
          'name' => string 'foo' (length=3)
          'category' => string 'category1' (length=9)
          'price' => float 11.5
      1 => 
        array (size=3)
          'name' => string 'bar1' (length=4)
          'category' => string 'category2' (length=9)
          'price' => float 9.5
  4 => 
    array (size=1)
      0 => 
        array (size=3)
          'name' => string 'barFoo' (length=6)
          'category' => string 'category1' (length=9)
          'price' => float 9.5
```
