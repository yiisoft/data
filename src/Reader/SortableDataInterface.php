<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

/**
 * @psalm-immutable
 */
interface SortableDataInterface
{
    /**
     * @param Sort|null $sorting
     * @return $this
     */
    public function withSort(?Sort $sorting): self;

    public function getSort(): ?Sort;
}
