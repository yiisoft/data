<?php

namespace Yiisoft\Data\Reader\Criterion;

final class LessThan implements CriteronInterface
{
    private $field;
    private $value;
    private $orEqual = false;

    public function __construct(string $field, $value)
    {
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException('Value should be scalar');
        }

        $this->field = $field;
        $this->value = $value;
    }

    public function orEqual(): self
    {
        $new = clone $this;
        $new->orEqual = true;
        return $new;
    }

    public function toArray(): array
    {
        return [$this->orEqual ? 'lte' : 'lt', $this->field, $this->value];
    }
}
