<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use InvalidArgumentException;

use function get_class;
use function gettype;
use function is_object;
use function is_scalar;
use function is_string;
use function sprintf;

final class FilterDataValidationHelper
{
    /**
     * @param mixed $field
     */
    public static function assertFieldIsString($field): void
    {
        if (!is_string($field)) {
            throw new InvalidArgumentException(sprintf(
                'The field should be string. The %s is received.',
                self::getValueType($field),
            ));
        }
    }

    /**
     * @param mixed $value
     */
    public static function assertIsScalar($value): void
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException(sprintf(
                'The value should be scalar. The %s is received.',
                is_object($value) ? get_class($value) : gettype($value),
            ));
        }
    }

    /**
     * @param mixed $value
     */
    public static function getValueType($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
