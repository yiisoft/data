<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader;

interface OffsetableDataInterface
{
    /**
     * @param int $offset
     * @return $this
     */
    public function withOffset(int $offset);
}
