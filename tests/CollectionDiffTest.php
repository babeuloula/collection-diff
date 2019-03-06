<?php
/**
 * @author      Wizaplace DevTeam <dev@wizaplace.com>
 * @copyright   Copyright (c) Wizaplace
 * @license     MIT
 */

declare(strict_types=1);

namespace Wizaplace\Test\CollectionDiff;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\{
    Normalizer\PropertyNormalizer,
    Serializer
};
use Wizaplace\CollectionDiff\{
    CollectionDiff,
    Exception\CollectionDiffException
};

class CollectionDiffTest extends TestCase
{
    /** @var CollectionDiff */
    protected $collectionDiff;

    protected $from = [];
    protected $to = [];

    public function setUp(): void
    {
        $this->collectionDiff = new CollectionDiff(
            new Serializer([
                new PropertyNormalizer(),
            ])
        );

        $this->from = [
            new FooObject("foo", "category1", 10.5),
            new FooObject("bar2", "category2", 9.5),
            new FooObject("bar1", "category1", 9.5),
            new FooObject("barFoo", "category1", 9.5),
            new FooObject("barFooBar", "category1", 9.5),
        ];

        $this->to = [
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
    }

    public function testCompareWithOldValues(): void
    {
        $this
            ->collectionDiff
            ->compare(['name', 'category'], $this->from, $this->to, true);

        static::assertCount(1, $this->collectionDiff->getNothing(), "[getNothing] should be equal to 1");
        static::assertSame($this->from[1], $this->collectionDiff->getNothing()[0]['newValues'], "[getNothing] is not equal to [from_1][newValues]");

        static::assertCount(1, $this->collectionDiff->getCreate(), "[getCreate] should be equal to 1");
        static::assertSame($this->to[3], $this->collectionDiff->getCreate()[0]['newValues'], "[getCreate] is not equal to [to_3][newValues]");
        static::assertSame($this->from[3], $this->collectionDiff->getCreate()[0]['oldValues'], "[getCreate] is not equal to [from_3][oldValues]");

        static::assertCount(2, $this->collectionDiff->getUpdate(), "[getUpdate] should be equal to 2");
        static::assertSame($this->to[0], $this->collectionDiff->getUpdate()[0]['newValues'], "[getUpdate] is not equal to [to_0][newValues]");
        static::assertSame($this->from[0], $this->collectionDiff->getUpdate()[0]['oldValues'], "[getUpdate] is not equal to [to_0][oldValues]");
        static::assertSame($this->to[2], $this->collectionDiff->getUpdate()[1]['newValues'], "[getUpdate] is not equal to [to_2][newValues]");
        static::assertSame($this->from[2], $this->collectionDiff->getUpdate()[1]['oldValues'], "[getUpdate] is not equal to [to_2][oldValues]");

        static::assertCount(2, $this->collectionDiff->getDelete(), "[getDelete] should be equal to 2");
        static::assertSame($this->from[3], $this->collectionDiff->getDelete()[0]['newValues'], "[getDelete] is not equal to [from_3][newValues]");
        static::assertSame($this->from[4], $this->collectionDiff->getDelete()[1]['newValues'], "[getDelete] is not equal to [from_4][newValues]");
    }

    public function testCompareWithoutOldValues(): void
    {
        $this
            ->collectionDiff
            ->compare(['name', 'category'], $this->from, $this->to);

        static::assertCount(1, $this->collectionDiff->getNothing(), "[getNothing] should be equal to 1");
        static::assertSame($this->from[1], $this->collectionDiff->getNothing()[0], "[getNothing] is not equal to [from_1]");

        static::assertCount(1, $this->collectionDiff->getCreate(), "[getCreate] should be equal to 1");
        static::assertSame($this->to[3], $this->collectionDiff->getCreate()[0], "[getCreate] is not equal to [to_3]");

        static::assertCount(2, $this->collectionDiff->getUpdate(), "[getUpdate] should be equal to 2");
        static::assertSame($this->to[0], $this->collectionDiff->getUpdate()[0], "[getUpdate] is not equal to [to_0]");
        static::assertSame($this->to[2], $this->collectionDiff->getUpdate()[1], "[getUpdate] is not equal to [to_2]");

        static::assertCount(2, $this->collectionDiff->getDelete(), "[getDelete] should be equal to 2");
        static::assertSame($this->from[3], $this->collectionDiff->getDelete()[0], "[getDelete] is not equal to [from_3]");
        static::assertSame($this->from[4], $this->collectionDiff->getDelete()[1], "[getDelete] is not equal to [from_4]");
    }

    public function testGetIncorrectMode(): void
    {
        static::expectException(CollectionDiffException::class);
        $this
            ->collectionDiff
            ->getActions(42);
    }
}
