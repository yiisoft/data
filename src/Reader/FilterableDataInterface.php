<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

interface FilterableDataInterface
{
    public function withFilter(FilterInterface $filter): self;

    public function withFilterProcessors(FilterProcessorInterface ...$filterProcessors): self;
}
