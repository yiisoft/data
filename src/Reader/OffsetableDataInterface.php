<?php
namespace Yiisoft\Data\Reader;

interface OffsetableDataInterface
{
    /**
     * @param int $offset
     * @return $this
     */
    public function withOffset(int $offset);
}
