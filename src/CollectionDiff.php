<?php
/**
 * @author      Wizaplace DevTeam <dev@wizaplace.com>
 * @copyright   Copyright (c) Wizaplace
 * @license     MIT
 */

declare(strict_types=1);

namespace Wizaplace\CollectionDiff;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class CollectionDiff
{
    public const NOTHING = "nothing";
    public const INSERT  = "insert";
    public const UPDATE  = "update";
    public const DELETE  = "delete";

    /** @var array */
    private $queries = [];

    /** @var array */
    private $primaryKeys;

    /** @var array */
    private $from;
    /** @var array */
    private $to;

    /** @var array */
    private $options = [
        'compareStrict'  => true,
        'defaultCompare' => 'todo',
    ];

    /** @var Serializer */
    private $serializer;

    /** @var bool */
    private $isAlreadyCompared = false;

    /**
     * @param NormalizerInterface[] $normalizers
     * @param string|array          $primaryKeys
     * @param array                 $from
     * @param array                 $to
     * @param array                 $options
     */
    public function __construct(array $normalizers, $primaryKeys, array $from, array $to, array $options = [])
    {
        $this->resetQueries();

        $this->primaryKeys = (true === is_string($primaryKeys)) ? [$primaryKeys] : $primaryKeys;

        $this->from = $from;
        $this->to   = $to;

        $this->options = array_merge($this->options, $options);

        $this->serializer = new Serializer($normalizers);
    }

    /**
     * @param string|null $mode
     *
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getQueries($mode = null): array
    {
        if (false === $this->isAlreadyCompared) {
            $this->{$this->options['defaultCompare']}();
        }

        if (!is_null($mode) && array_key_exists($mode, $this->queries)) {
            return $this->queries[$mode];
        }

        return $this->queries;
    }

    /**
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getNothing(): array
    {
        return $this->getQueries(static::NOTHING);
    }

    /**
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getInsert(): array
    {
        return $this->getQueries(static::INSERT);
    }

    /**
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getUpdate(): array
    {
        return $this->getQueries(static::UPDATE);
    }

    /**
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function getDelete(): array
    {
        return $this->getQueries(static::DELETE);
    }

    /**
     * @param NormalizerInterface[] $normalizers
     *
     * @return CollectionDiff
     */
    public function setNormalizers(array $normalizers): CollectionDiff
    {
        $this->serializer = new Serializer($normalizers);

        return $this;
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    private function compare(): void
    {
        if (false === $this->isAlreadyCompared) {
            foreach ($this->to as $key => $values) {
                $from = $this->from[$key];

                if (array_key_exists($key, $this->from)) {
                    /** @var array $normalizeValues */
                    $normalizeValues = $this->serializer->normalize($values);
                    /** @var array $normalizeFrom */
                    $normalizeFrom   = $this->serializer->normalize($from);

                    $action = static::NOTHING;
                    foreach ($this->primaryKeys as $primaryKey) {
                        if (
                            false === array_key_exists($primaryKey, $normalizeValues) ||
                            false === array_key_exists($primaryKey, $normalizeFrom)
                        ) {
                            continue;
                        }

                        if (
                            (
                                true === $this->options['compareStrict'] &&
                                $normalizeValues[$primaryKey] === $normalizeFrom[$primaryKey]
                            ) ||
                            (
                                false === $this->options['compareStrict'] &&
                                $normalizeValues[$primaryKey] == $normalizeFrom[$primaryKey]
                            )
                        ) {
                            foreach ($normalizeValues as $k => $v) {
                                if (false === array_key_exists($k, $normalizeFrom) || $v !== $normalizeFrom[$k]) {
                                    $action = static::UPDATE;

                                    $this->add(static::UPDATE, $values, $from);
                                    break;
                                }
                            }

                            if (static::NOTHING !== $action) {
                                break;
                            }
                        } else {
                            $action = static::DELETE;

                            $this->add(static::DELETE, $from);
                            $this->add(static::INSERT, $values, $from);

                            break;
                        }
                    }

                    if (static::NOTHING === $action) {
                        $this->add(static::NOTHING, $from);
                    }
                } else {
                    $this->add(static::INSERT, $values);
                }

                unset($this->from[$key]);
            }

            if (count($this->from) > 0) {
                foreach ($this->from as $key => $from) {
                    $this->add(static::DELETE, $from);
                    unset($this->from[$key]);
                }
            }

            $this->isAlreadyCompared = true;
        }
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    private function todo(): void
    {
        if (false === $this->isAlreadyCompared) {
            $this->compare();
        }

        $queries = $this->getQueries();
        $this->resetQueries();

        foreach ($queries as $mode => $data) {
            foreach ($data as $values) {
                $this->add($mode, $values['newValues'], null, 'todo');
            }
        }
    }

    /**
     * @param string $mode
     * @param mixed  $newValues
     * @param mixed  $oldValues
     * @param string $method
     */
    private function add($mode, $newValues, $oldValues = null, string $method = 'compare'): void
    {
        if ('compare' === $method) {
            $data = [
                'newValues' => $newValues,
            ];

            if (null !== $oldValues) {
                $data['oldValues'] = $oldValues;
            }
        } else {
            $data = $newValues;
        }

        $this->queries[$mode][] = $data;
    }

    private function resetQueries(): void
    {
        $this->queries = [
            static::NOTHING => [],
            static::INSERT  => [],
            static::UPDATE  => [],
            static::DELETE  => [],
        ];
    }
}
