<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use InvalidArgumentException;

use function get_class;
use function gettype;
use function is_scalar;
use function is_object;
use function sprintf;

final class Between implements FilterInterface
{
    private string $field;

    /**
     * @var bool|float|int|string
     */
    private $firstValue;

    /**
     * @var bool|float|int|string
     */
    private $secondValue;

    /**
     * @param string $field
     * @param bool|float|int|string $firstValue
     * @param bool|float|int|string $secondValue
     */
    public function __construct(string $field, $firstValue, $secondValue)
    {
        $this->field = $field;

        $this->validateValue($firstValue);
        $this->validateValue($secondValue);

        $this->firstValue = $firstValue;
        $this->secondValue = $secondValue;
    }

    public function toArray(): array
    {
        return [self::getOperator(), $this->field, $this->firstValue, $this->secondValue];
    }

    public static function getOperator(): string
    {
        return 'between';
    }

    /**
     * @param mixed $value
     */
    private function validateValue($value): void
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException(sprintf(
                'The value should be scalar. The %s is received.',
                is_object($value) ? get_class($value) : gettype($value),
            ));
        }
    }
}
