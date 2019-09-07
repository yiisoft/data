<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;


class Equals implements FilterInterface
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
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException('Value should be scalar');
        }
    }

    public function toArray(): array
    {
        return [self::getOperator(), $this->field, $this->value];
    }

    public static function getOperator(): string
    {
        return 'eq';
    }
}
