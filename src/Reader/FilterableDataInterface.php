<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\FilterHandlerInterface;

interface FilterableDataInterface
{
    public function withFilter(FilterInterface $filter): static;

    public function withFilterProcessors(FilterHandlerInterface ...$filterProcessors): static;
}
