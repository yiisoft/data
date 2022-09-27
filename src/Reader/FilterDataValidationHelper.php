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
    public static function assertFieldIsString(mixed $field): void
    {
        if (!is_string($field)) {
            throw new InvalidArgumentException(sprintf(
                'The field should be string. The %s is received.',
                self::getValueType($field),
            ));
        }
    }

    public static function assertFilterProcessorIsIterable(mixed $filterProcessor): void
    {
        if (!$filterProcessor instanceof IterableProcessorInterface) {
            throw new InvalidArgumentException(sprintf(
                'The filter processor should be an object and implement "%s". The %s is received.',
                IterableProcessorInterface::class,
                self::getValueType($filterProcessor),
            ));
        }
    }

    public static function assertIsScalar(mixed $value): void
    {
        if (!is_scalar($value)) {
            throw new InvalidArgumentException(sprintf(
                'The value should be scalar. The %s is received.',
                self::getValueType($value),
            ));
        }
    }

    public static function assertIsScalarOrInstanceOfDataTimeInterface(mixed $value): void
    {
        if (!$value instanceof DateTimeInterface && !is_scalar($value)) {
            throw new InvalidArgumentException(sprintf(
                'The value should be scalar or %s instance. The %s is received.',
                DateTimeInterface::class,
                self::getValueType($value),
            ));
        }
    }

    public static function getValueType(mixed $value): string
    {
        return get_debug_type($value);
    }
}
