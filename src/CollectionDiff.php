<?php
/**
 * @author      Wizaplace DevTeam <dev@wizaplace.com>
 * @copyright   Copyright (c) Wizaplace
 * @license     MIT
 */

declare(strict_types=1);

namespace Wizaplace\CollectionDiff;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Wizaplace\CollectionDiff\Exception\CollectionDiffException;

class CollectionDiff
{
    public const ACTION_NOTHING = 1;
    public const ACTION_CREATE = 2;
    public const ACTION_UPDATE = 3;
    public const ACTION_DELETE = 4;

    protected $actions = [
        self::ACTION_NOTHING => [],
        self::ACTION_CREATE  => [],
        self::ACTION_UPDATE  => [],
        self::ACTION_DELETE  => [],
    ];
    protected $primaryKeys = [];
    protected $from = [];
    protected $to = [];

    /** @var NormalizerInterface */
    protected $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function compare(array $primaryKeys, array $from, array $to, bool $addOldValues = false, bool $strictComparison = true): self
    {
        $this->resetActions();

        $this->primaryKeys = $primaryKeys;

        $this->from = $from;
        $this->to = $to;

        $this->doCompare($addOldValues, $strictComparison);

        return $this;
    }

    public function getActions(int $action = null): array
    {
        if (false === is_null($action)) {
            if (false === array_key_exists($action, $this->actions)) {
                throw new CollectionDiffException("Unable to find this action.");
            }

            return $this->actions[$action];
        }

        return $this->actions;
    }

    public function getNothing(): array
    {
        return $this->getActions(static::ACTION_NOTHING);
    }

    public function getCreate(): array
    {
        return $this->getActions(static::ACTION_CREATE);
    }

    public function getUpdate(): array
    {
        return $this->getActions(static::ACTION_UPDATE);
    }

    public function getDelete(): array
    {
        return $this->getActions(static::ACTION_DELETE);
    }

    protected function doCompare(bool $addOldValues, bool $strictComparison): void
    {
        foreach ($this->to as $key => $values) {
            if (true === array_key_exists($key, $this->from)) {
                $from = $this->from[$key];

                /** @var array $normalizeValues */
                $normalizeValues = $this->normalizer->normalize($values);
                /** @var array $normalizeFrom */
                $normalizeFrom   = $this->normalizer->normalize($from);

                $action = static::ACTION_NOTHING;
                foreach ($this->primaryKeys as $primaryKey) {
                    if (
                        false === array_key_exists($primaryKey, $normalizeValues) ||
                        false === array_key_exists($primaryKey, $normalizeFrom)
                    ) {
                        continue;
                    }

                    if (
                        (
                            true === $strictComparison &&
                            $normalizeValues[$primaryKey] === $normalizeFrom[$primaryKey]
                        ) ||
                        (
                            false === $strictComparison &&
                            $normalizeValues[$primaryKey] == $normalizeFrom[$primaryKey]
                        )
                    ) {
                        foreach ($normalizeValues as $k => $v) {
                            if (false === array_key_exists($k, $normalizeFrom) || $v !== $normalizeFrom[$k]) {
                                $action = static::ACTION_UPDATE;

                                $this->add(static::ACTION_UPDATE, $values, $from, $addOldValues);
                                break;
                            }
                        }

                        if (static::ACTION_NOTHING !== $action) {
                            break;
                        }
                    } else {
                        $action = static::ACTION_DELETE;

                        $this->add(static::ACTION_DELETE, $from, null, $addOldValues);
                        $this->add(static::ACTION_CREATE, $values, $from, $addOldValues);

                        break;
                    }
                }

                if (static::ACTION_NOTHING === $action) {
                    $this->add(static::ACTION_NOTHING, $from, null, $addOldValues);
                }

                unset($this->from[$key]);
            } else {
                $this->add(static::ACTION_CREATE, $values, null, $addOldValues);
            }
        }

        if (count($this->from) > 0) {
            foreach ($this->from as $key => $from) {
                $this->add(static::ACTION_DELETE, $from, null, $addOldValues);
                unset($this->from[$key]);
            }
        }
    }

    /**
     * @param mixed      $newValues
     * @param mixed|null $oldValues
     */
    protected function add(int $action, $newValues, $oldValues = null, bool $addOldValues = false): void
    {
        if (true === $addOldValues) {
            $data = [
                'newValues' => $newValues,
            ];

            if (null !== $oldValues) {
                $data['oldValues'] = $oldValues;
            }
        } else {
            $data = $newValues;
        }

        $this->actions[$action][] = $data;
    }

    protected function resetActions(): void
    {
        $this->actions = [
            static::ACTION_NOTHING => [],
            static::ACTION_CREATE  => [],
            static::ACTION_UPDATE  => [],
            static::ACTION_DELETE  => [],
        ];
    }
}
