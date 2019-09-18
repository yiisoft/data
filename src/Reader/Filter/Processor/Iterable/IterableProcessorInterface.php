<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor\Iterable;

interface IterableProcessorInterface
{
    public function match(array $item, array $arguments, array $filterProcessors): bool;
}
