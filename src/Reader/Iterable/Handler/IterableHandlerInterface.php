<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Handler;

use Yiisoft\Data\Reader\Filter\FilterHandlerInterface;

interface IterableHandlerInterface extends FilterHandlerInterface
{
    public function match(array|object $item, array $arguments, array $filterHandlers): bool;
}
