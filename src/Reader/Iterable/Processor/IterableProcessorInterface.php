<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

interface IterableProcessorInterface
{
    public function match(array $item, array $arguments, array $filterProcessors): bool;
}
