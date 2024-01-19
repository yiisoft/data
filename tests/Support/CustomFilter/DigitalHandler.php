<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Support\CustomFilter;

use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

final class DigitalHandler implements IterableFilterHandlerInterface
{
    public function getOperator(): string
    {
        return Digital::getOperator();
    }

    public function match(object|array $item, array $arguments, array $iterableFilterHandlers): bool
    {
        return ctype_digit((string) ArrayHelper::getValue($item, $arguments[0]));
    }
}
