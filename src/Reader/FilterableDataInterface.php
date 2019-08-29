<?php
namespace Yiisoft\Data\Reader;

interface FilterableDataInterface
{
    public function withFilter(Filter $filter);
}
