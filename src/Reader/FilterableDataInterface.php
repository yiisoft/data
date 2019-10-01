<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

interface FilterableDataInterface
{
    /**
     * @return static
     */
    public function withFilter(FilterInterface $filter);
    public function withFilterProcessors(FilterProcessorInterface... $filterProcessors);
}
