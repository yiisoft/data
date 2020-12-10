<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use function array_key_exists;
use function is_array;
use function is_int;
use function is_string;

/**
 * Sort represents information relevant to sorting according to one or multiple item fields.
 *
 * @template TConfigItem as array{asc: mixed, desc: mixed, default: string, label: string}
 * @template TConfig as array<string, TConfigItem>
 * @psalm-immutable
 */
final class Sort
{
    /** @var TConfig */
    private array $config;

    /**
     * @var array Field names to order by as keys, direction as values.
     */
    private array $currentOrder = [];

    /**
     * @var array<int, string>|array<string, array<string, int|string>> $config A list of sortable fields along with their
     * configuration.
     *
     * ```php
     * [
     *     'age', // means will be sorted as is
     *     'name' => [
     *         'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
     *         'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
     *         'default' => 'desc',
     *         'label' => 'Name',
     *     ],
     * ]
     * ```
     *
     * In the above, two fields are declared: `age` and `name`. The `age` field is
     * a simple field which is equivalent to the following:
     *
     * ```php
     * 'age' => [
     *     'asc' => ['age' => SORT_ASC],
     *     'desc' => ['age' => SORT_DESC],
     *     'default' => 'asc',
     *     'label' => Inflector::camel2words('age'),
     * ]
     * ```
     *
     * The name field is a virtual field name that consists of two real fields, `first_name` amd `last_name`. Virtual
     * field name is used in order string or order array while real fields are used in final sorting criteria.
     *
     * Each configuration has the following options:
     *
     * - `asc` - criteria for ascending sorting.
     * - `desc` - criteria for descending sorting.
     * - `default` - default sorting. Could be either `asc` or `desc`. If not specified, `asc` is used.
     * - `label` -
     */
    public function __construct(array $config)
    {
        $normalizedConfig = [];
        foreach ($config as $fieldName => $fieldConfig) {
            if (!(is_int($fieldName) && is_string($fieldConfig) || is_string($fieldName) && is_array($fieldConfig))) {
                throw new \InvalidArgumentException('Invalid config format.');
            }

            if (is_string($fieldConfig)) {
                $fieldName = $fieldConfig;
                $fieldConfig = [];
            }

            /** @var TConfig $fieldConfig */
            $normalizedConfig[$fieldName] = array_merge([
                'asc' => [$fieldName => SORT_ASC],
                'desc' => [$fieldName => SORT_DESC],
                'default' => 'asc',
                'label' => $fieldName,
            ], $fieldConfig);
        }

        /** @var TConfig $normalizedConfig */
        $this->config = $normalizedConfig;
    }

    /**
     * Change sorting order based on order string.
     *
     * The format must be the field name only for ascending
     * or the field name prefixed with `-` for descending.
     *
     * @param string $orderString
     *
     * @return $this
     */
    public function withOrderString(string $orderString): self
    {
        $order = [];
        $parts = preg_split('/\s*,\s*/', trim($orderString), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $part) {
            if (strpos($part, '-') === 0) {
                $order[substr($part, 1)] = 'desc';
            } else {
                $order[$part] = 'asc';
            }
        }
        return $this->withOrder($order);
    }

    /**
     * @param array $order Field names to order by as keys, direction as values.
     *
     * @return $this
     */
    public function withOrder(array $order): self
    {
        $new = clone $this;
        $new->currentOrder = $order;
        return $new;
    }

    public function getOrder(): array
    {
        return $this->currentOrder;
    }

    public function getOrderAsString(): string
    {
        $parts = [];
        foreach ($this->currentOrder as $field => $direction) {
            $parts[] = ($direction === 'desc' ? '-' : '') . $field;
        }
        return implode(',', $parts);
    }

    /**
     * Final sorting criteria to apply.
     */
    public function getCriteria(): array
    {
        $criteria = [];
        $order = $this->getOrder();

        $config = $this->config;

        foreach ($order as $field => $direction) {
            if (!array_key_exists($field, $config)) {
                continue;
            }

            $criteria += $config[$field][$direction];

            unset($config[$field]);
        }

        foreach ($config as $field => $fieldConfig) {
            $criteria += $fieldConfig[$fieldConfig['default']];
        }

        return $criteria;
    }
}
