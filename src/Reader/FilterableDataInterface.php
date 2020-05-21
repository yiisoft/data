<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

interface FilterableDataInterface
{
    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function withFilter(FilterInterface $filter);
    /**
     * @param FilterProcessorInterface[] $filterProcessors
     * @return $this
     */
    public function withFilterProcessors(FilterProcessorInterface ...$filterProcessors);
}
