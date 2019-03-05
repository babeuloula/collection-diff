<?php
/**
 * @author      Wizaplace DevTeam <dev@wizaplace.com>
 * @copyright   Copyright (c) Wizaplace
 * @license     MIT
 */

declare(strict_types=1);

namespace Wizaplace\Test\CollectionDiff;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Wizaplace\CollectionDiff\CollectionDiff;

class CollectionDiffTest extends TestCase
{
    public function testCompareWithCompare()
    {
        $from = [
            new RandomObject("foo", "category1", 10.5),
            new RandomObject("bar2", "category2", 9.5),
            new RandomObject("bar1", "category1", 9.5),
            new RandomObject("barFoo", "category1", 9.5),
            new RandomObject("barFooBar", "category1", 9.5),
        ];

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

        $collectionUpdater = new CollectionDiff([new PropertyNormalizer()], ['name', 'category'], $from, $to, ['defaultCompare' => 'compare']);

        $this->assertCount(1, $collectionUpdater->getNothing(), "[getNothing] should be equal to 1");
        $this->assertSame($from[1], $collectionUpdater->getNothing()[0]['newValues'], "[getNothing] is not equal to [from_1][newValues]");

        $this->assertCount(1, $collectionUpdater->getInsert(), "[getInsert] should be equal to 1");
        $this->assertSame($to[3], $collectionUpdater->getInsert()[0]['newValues'], "[getInsert] is not equal to [to_3][newValues]");
        $this->assertSame($from[3], $collectionUpdater->getInsert()[0]['oldValues'], "[getInsert] is not equal to [from_3][oldValues]");

        $this->assertCount(2, $collectionUpdater->getUpdate(), "[getUpdate] should be equal to 2");
        $this->assertSame($to[0], $collectionUpdater->getUpdate()[0]['newValues'], "[getUpdate] is not equal to [to_0][newValues]");
        $this->assertSame($from[0], $collectionUpdater->getUpdate()[0]['oldValues'], "[getUpdate] is not equal to [to_0][oldValues]");
        $this->assertSame($to[2], $collectionUpdater->getUpdate()[1]['newValues'], "[getUpdate] is not equal to [to_2][newValues]");
        $this->assertSame($from[2], $collectionUpdater->getUpdate()[1]['oldValues'], "[getUpdate] is not equal to [to_2][oldValues]");

        $this->assertCount(2, $collectionUpdater->getDelete(), "[getDelete] should be equal to 2");
        $this->assertSame($from[3], $collectionUpdater->getDelete()[0]['newValues'], "[getDelete] is not equal to [from_3][newValues]");
        $this->assertSame($from[4], $collectionUpdater->getDelete()[1]['newValues'], "[getDelete] is not equal to [from_4][newValues]");
    }

    public function testCompareWithTodo()
    {
        $from = [
            new RandomObject("foo", "category1", 10.5),
            new RandomObject("bar2", "category2", 9.5),
            new RandomObject("bar1", "category1", 9.5),
            new RandomObject("barFoo", "category1", 9.5),
            new RandomObject("barFooBar", "category1", 9.5),
        ];

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

        $collectionUpdater = new CollectionDiff([new PropertyNormalizer()], ['name', 'category'], $from, $to);

        $this->assertCount(1, $collectionUpdater->getNothing(), "[getNothing] should be equal to 1");
        $this->assertSame($from[1], $collectionUpdater->getNothing()[0], "[getNothing] is not equal to [from_1]");

        $this->assertCount(1, $collectionUpdater->getInsert(), "[getInsert] should be equal to 1");
        $this->assertSame($to[3], $collectionUpdater->getInsert()[0], "[getInsert] is not equal to [to_3]");

        $this->assertCount(2, $collectionUpdater->getUpdate(), "[getUpdate] should be equal to 2");
        $this->assertSame($to[0], $collectionUpdater->getUpdate()[0], "[getUpdate] is not equal to [to_0]");
        $this->assertSame($to[2], $collectionUpdater->getUpdate()[1], "[getUpdate] is not equal to [to_2]");

        $this->assertCount(2, $collectionUpdater->getDelete(), "[getDelete] should be equal to 2");
        $this->assertSame($from[3], $collectionUpdater->getDelete()[0], "[getDelete] is not equal to [from_3]");
        $this->assertSame($from[4], $collectionUpdater->getDelete()[1], "[getDelete] is not equal to [from_4]");
    }
}
