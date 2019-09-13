<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\Processor\FilterProcessor;

interface FilterableDataInterface
{
    public function withFilter(FilterInterface $filter);
    public function getFilterProcessor(): FilterProcessor;
}
