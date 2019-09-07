<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Criterion;

interface CriteronInterface
{
    public static function getOperator(): string;
    public function toArray(): array;
}
