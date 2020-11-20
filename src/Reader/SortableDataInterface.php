<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

interface SortableDataInterface
{
    /**
     * @param Sort|null $sorting
     *
     * @return $this
     *
     * @psalm-mutation-free
     */
    public function withSort(?Sort $sorting): self;

    public function getSort(): ?Sort;
}
