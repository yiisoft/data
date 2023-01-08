<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\IterableFilterHandler;

use DateTimeInterface;
use InvalidArgumentException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\FilterAssertHelper;
use Yiisoft\Data\Reader\IterableFilterHandlerInterface;

use function count;

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
        [$field, $firstValue, $secondValue] = $arguments;
        FilterAssertHelper::assertFieldIsString($field);

        $value = ArrayHelper::getValue($item, $field);

        if (!$value instanceof DateTimeInterface) {
            return $value >= $firstValue && $value <= $secondValue;
        }

        return $firstValue instanceof DateTimeInterface
            && $secondValue instanceof DateTimeInterface
            && $value->getTimestamp() >= $firstValue->getTimestamp()
            && $value->getTimestamp() <= $secondValue->getTimestamp();
    }
}
