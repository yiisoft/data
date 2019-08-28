<?php
namespace Yiisoft\Data\Reader;

interface SortableDataInterface
{
    /**
     * @param Sort|null $sorting
     * @return $this
     */
    public function withSorting(?Sort $sorting);
}
