<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

interface SortableDataInterface
{
    public function withSort(?Sort $sort): self;

    public function getSort(): ?Sort;
}
