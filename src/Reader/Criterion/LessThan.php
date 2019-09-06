<?php

namespace Yiisoft\Data\Reader\Criterion;

final class LessThan implements CriteronInterface
{
    private $field;
    private $value;

    public function __construct(string $field, $value)
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException('Value should be scalar');
        }

        $this->field = $field;
        $this->value = $value;
    }

    public function toArray(): array
    {
        return ['lt', $this->field, $this->value];
    }
}
