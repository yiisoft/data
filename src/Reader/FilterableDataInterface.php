<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use Yiisoft\Data\Reader\Filter\FilterInterface;

interface FilterableDataInterface
{
    public function withFilter(FilterInterface $filter);
}
