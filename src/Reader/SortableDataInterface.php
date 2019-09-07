<?php
namespace Yiisoft\Data\Reader;

interface SortableDataInterface
{
    /**
     * @param Sort|null $sorting
     * @return $this
     */
    public function withSort(?Sort $sorting);

    public function getSort(): ?Sort;
}
