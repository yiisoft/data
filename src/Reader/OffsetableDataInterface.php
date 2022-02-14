<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

interface OffsetableDataInterface
{
    public function withOffset(int $offset): self;
}
