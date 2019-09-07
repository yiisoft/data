<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

final class GreaterThanOrEqual implements FilterInterface
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
        return [self::getOperator(), $this->field, $this->value];
    }

    public static function getOperator(): string
    {
        return 'gte';
    }
}
