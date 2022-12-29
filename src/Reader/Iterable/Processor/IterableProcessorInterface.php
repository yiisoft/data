<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

interface IterableProcessorInterface extends FilterProcessorInterface
{
    public function match(array|object $item, array $arguments, array $filterProcessors): bool;
}
