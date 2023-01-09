<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\FilterHandler;

use DateTimeInterface;
use InvalidArgumentException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\FilterAssertHelper;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

use function count;

/**
 * Between iterable filter handler checks that the item's field value
 * is between minimal and maximal values.
 */
final class Between implements IterableFilterHandlerInterface
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\Between::getOperator();
    }

    public function match(array|object $item, array $arguments, array $iterableFilterHandlers): bool
    {
        if (count($arguments) !== 3) {
            throw new InvalidArgumentException('$arguments should contain exactly three elements.');
        }

        /** @var string $field */
        [$field, $minimalValue, $maximalValue] = $arguments;
        FilterAssertHelper::assertFieldIsString($field);

        $value = ArrayHelper::getValue($item, $field);

        if (!$value instanceof DateTimeInterface) {
            return $value >= $minimalValue && $value <= $maximalValue;
        }

        return $minimalValue instanceof DateTimeInterface
            && $maximalValue instanceof DateTimeInterface
            && $value->getTimestamp() >= $minimalValue->getTimestamp()
            && $value->getTimestamp() <= $maximalValue->getTimestamp();
    }
}
