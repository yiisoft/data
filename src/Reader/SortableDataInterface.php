<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

interface SortableDataInterface
{
    /**
     * @param Sort|null $sorting
     *
     * @return static
     *
     * @psalm-mutation-free
     */
    public function withSort(?Sort $sort): self;

    public function getSort(): ?Sort;
}
