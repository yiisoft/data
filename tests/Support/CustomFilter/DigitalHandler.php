<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Support\CustomFilter;

use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

final class DigitalHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return Digital::class;
    }

    public function match(object|array $item, FilterInterface $filter, array $iterableFilterHandlers): bool
    {
        /** @var Digital $filter */
        return ctype_digit((string) ArrayHelper::getValue($item, $filter->field));
    }
}
