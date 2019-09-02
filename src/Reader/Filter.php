<?php

namespace Yiisoft\Data\Reader;

final class Filter
{
    private const EQUALS = 'eq';
    private const LESS_THAN = 'lt';
    private const LESS_THAN_OR_EQUAL = 'lte';
    private const GREATER_THAN = 'gt';
    private const GREATER_THAN_OR_EQUAL = 'gte';
    private const IN = 'in';
    private const LIKE = 'like';
    private const NOT = 'not';
    private const AND = 'and';
    private const OR = 'or';

    private $criteria;

    public function __construct(array $criteria)
    {
        $this->validateCriteria($criteria);
        $this->criteria = $criteria;
    }

    private function validateCriteria(array $criteria): void
    {
        foreach ($criteria as $key => $value) {
            if ($key === self::OR || $key === self::AND) {
                foreach ($value as $subcriteria) {
                    $this->validateCriteria($subcriteria);
                }
            } elseif (is_int($key) && is_array($value)) {
                if ($value === []) {
                    throw new \InvalidArgumentException('Invalid criteria format near []');
                }
                $operator = array_shift($value);
                $this->validateOperatorCriteria($operator, $value);
            } else {
                throw new \InvalidArgumentException("Invalid criteria format near \"$key\"");
            }
        }
    }

    private function validateOperatorCriteria(string $operator, array $operands): void
    {
        // TODO: implement. Preferably with ease of extension.
    }

    public function getCriteria(): array
    {
        return $this->criteria;
    }
}
