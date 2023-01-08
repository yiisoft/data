<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Handler;

use DateTimeInterface;
use InvalidArgumentException;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\FilterDataValidationHelper;

use function count;

final class Between implements IterableHandlerInterface
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\Between::getOperator();
    }

    public function match(array|object $item, array $arguments, array $filterHandlers): bool
    {
        if (count($arguments) !== 3) {
            throw new InvalidArgumentException('$arguments should contain exactly three elements.');
        }

        /** @var string $field */
        [$field, $firstValue, $secondValue] = $arguments;
        FilterDataValidationHelper::assertFieldIsString($field);

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
