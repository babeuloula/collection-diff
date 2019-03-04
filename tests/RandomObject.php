<?php
/**
 * @author      Wizaplace DevTeam <dev@wizaplace.com>
 * @copyright   Copyright (c) Wizaplace
 * @license     MIT
 */

declare(strict_types=1);

namespace Wizaplace\Test\CollectionDiff;

class RandomObject
{
    /** @var string */
    private $name;

    /** @var string */
    private $category;

    /** @var float */
    private $price;

    public function __construct(string $name, string $category, float $price)
    {
        $this->name = $name;
        $this->category = $category;
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }
}
