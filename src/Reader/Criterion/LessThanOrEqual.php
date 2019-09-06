<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Criterion;

final class LessThanOrEqual implements CriteronInterface
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
        return ['lte', $this->field, $this->value];
    }
}
