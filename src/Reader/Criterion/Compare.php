<?php

namespace Yiisoft\Data\Reader\Criterion;


final class Compare implements CriteronInterface
{
    private $field;
    private $value;

    public function __construct(string $field, $value)
    {
        $this->validateValue($value);

        $this->field = $field;
        $this->value = $value;
    }

    private function validateValue($value): void
    {
        if ($value === null || is_scalar($value)) {
            return;
        }

        if (is_array($value)) {
            foreach ($value as $arrayValue) {
                if (!is_scalar($arrayValue)) {
                    throw new \InvalidArgumentException('All array values should be scalar');
                }
            }
        }
    }

    public function toArray(): array
    {
        return [is_array($this->value) ? 'in' : 'eq', $this->field, $this->value];
    }
}
