<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use DateTimeInterface;
use InvalidArgumentException;
use Yiisoft\Data\Reader\Iterable\Processor\IterableProcessorInterface;

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
     * @param mixed $filterProcessor
     */
    public static function assertFilterProcessorIsIterable($filterProcessor): void
    {
        if (!$filterProcessor instanceof IterableProcessorInterface) {
            throw new InvalidArgumentException(sprintf(
                'The filter processor should be an object and implement "%s". The %s is received.',
                IterableProcessorInterface::class,
                self::getValueType($filterProcessor),
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
                self::getValueType($value),
            ));
        }
    }

    /**
     * @param mixed $value
     */
    public static function assertIsScalarOrInstanceOfDataTimeInterface($value): void
    {
        if (!$value instanceof DateTimeInterface && !is_scalar($value)) {
            throw new InvalidArgumentException(sprintf(
                'The value should be scalar or %s instance. The %s is received.',
                DateTimeInterface::class,
                self::getValueType($value),
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
