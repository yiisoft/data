<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Criterion;

interface CriteronInterface
{
    public function toArray(): array;
}
