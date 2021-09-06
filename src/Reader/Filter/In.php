<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use InvalidArgumentException;

use function is_array;

final class In implements FilterInterface
{
    private string $field;
    private array $value;

    public function __construct(string $field, array $value)
    {
        $this->validateValue($value);

        $this->field = $field;
        $this->value = $value;
    }

    /**
     * @param mixed $value
     */
    private function validateValue($value): void
    {
        if (is_array($value)) {
            foreach ($value as $arrayValue) {
                if (!is_scalar($arrayValue)) {
                    throw new InvalidArgumentException('All array values should be scalar');
                }
            }
        }
    }

    public function toArray(): array
    {
        return [self::getOperator(), $this->field, $this->value];
    }

    public static function getOperator(): string
    {
        return 'in';
    }
}
