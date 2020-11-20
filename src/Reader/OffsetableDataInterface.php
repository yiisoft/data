<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

interface OffsetableDataInterface
{
    /**
     * @param int $offset
     *
     * @return $this
     *
     * @psalm-mutation-free
     */
    public function withOffset(int $offset): self;
}
