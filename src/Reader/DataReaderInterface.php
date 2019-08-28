<?php
namespace Yiisoft\Data\Reader;

interface DataReaderInterface
{
    public const DEFAULT_LIMIT = 10;

    /**
     * @param int $limit
     * @return $this
     */
    public function withLimit(int $limit);
    public function read(): iterable;
}
