<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

interface SortableDataInterface
{
    public function withSort(?Sort $sort): static;

    public function getSort(): ?Sort;
}
