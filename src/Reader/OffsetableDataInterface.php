<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

interface OffsetableDataInterface
{
    /**
     * @psalm-return static
     */
    public function withOffset(int $offset): static;
}
