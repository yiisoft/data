<?php
namespace Yiisoft\Data\Reader;

use Yiisoft\Data\Reader\Filter\FilterInterface;

interface FilterableDataInterface
{
    public function withFilter(FilterInterface $criteron);
}
