<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

interface FilterableDataInterface
{
    /**
     * @param FilterInterface $filter
     *
     * @return static
     *
     * @psalm-mutation-free
     */
    public function withFilter(FilterInterface $filter): self;

    /**
     * @param FilterProcessorInterface[] $filterProcessors
     *
     * @return static
     *
     * @psalm-mutation-free
     */
    public function withFilterProcessors(FilterProcessorInterface ...$filterProcessors): self;
}
