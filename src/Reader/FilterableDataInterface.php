<?php
namespace Yiisoft\Data\Reader;

interface FilterableDataInterface
{
    public function filter(Filter $filter);
}
