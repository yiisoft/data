<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Support\CustomFilter;

use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;

final class DigitalHandler implements IterableFilterHandlerInterface
{
    public function getFilterClass(): string
    {
        return Digital::class;
    }

    public function match(object|array $item, FilterInterface $filter, Context $context): bool
    {
        /** @var Digital $filter */
        return ctype_digit((string) $context->readValue($item, $filter->field));
    }
}
