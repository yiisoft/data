<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader;

/**
 * Sort represents information relevant to sorting according to one or multiple item fields.
 */
final class Sort
{
    /**
     * @var array
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
     */
    private $config;

    /**
     * @var array field names to order by as keys, direction as values
     */
    private $currentOrder = [];

    public function __construct(array $config)
    {
        $normalizedConfig = [];
        foreach ($config as $fieldName => $fieldConfig) {
            if (
                !(is_int($fieldName) && is_string($fieldConfig))
                && !(is_string($fieldName) && is_array($fieldConfig))
            ) {
                throw new \InvalidArgumentException('Invalid config format');
            }

            if (is_string($fieldConfig)) {
                $fieldName = $fieldConfig;
                $fieldConfig = [];
            }

            if (!isset($fieldConfig['asc'], $fieldConfig['desc'])) {
                $normalizedConfig[$fieldName] = array_merge([
                    'asc' => [$fieldName => SORT_ASC],
                    'desc' => [$fieldName => SORT_DESC],
                    'default' => 'asc',
                    'label' => $fieldName,
                ], $fieldConfig);
            } else {
                $normalizedConfig[$fieldName] = $fieldConfig;
            }
        }

        $this->config = $normalizedConfig;
    }

    /**
     * Change sorting order based on order string.
     *
     * The format must be the field name only for ascending
     * or the field name prefixed with `-` for descending.
     *
     * @param string $orderString
     * @return Sort
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
     * @param array $order field names to order by as keys, direction as values
     * @return Sort
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

    public function getCriteria(): array
    {
        $criteria = [];
        foreach ($this->getOrder() as $field => $direction) {
            if (isset($this->config[$field][$direction])) {
                $criteria = array_merge($criteria, $this->config[$field][$direction]);
            }
        }
        return $criteria;
    }
}
