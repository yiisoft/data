<?php
namespace Yiisoft\Data\Reader;

use Yiisoft\Data\Reader\Criterion\CriteronInterface;

interface FilterableDataInterface
{
    public function withFilter(CriteronInterface $criteron);
}
