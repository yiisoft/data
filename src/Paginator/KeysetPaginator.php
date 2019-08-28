<?php
namespace Yiisoft\Data\Paginatior;

use Yiisoft\Data\Reader\DataReaderInterface;

/**
 * Keyset paginator
 *
 * - Equally fast for 1st and 1000nd page
 * - Total number of pages is not available
 * - Cannot get to specific page, only "next" and "previous"
 *
 * @link https://use-the-index-luke.com/no-offset
 */
class KeysetPaginator
{
    private $dataReader;
    private $pageSize;

    private $lastField;
    private $lastValue;

    public function __construct(DataReaderInterface $dataReader)
    {
        $this->dataReader = $dataReader;
    }

    public function read(): iterable
    {
        $dataReader = $this->dataReader->withLimit($this->pageSize);
        yield from $dataReader->read();
    }

    public function withLast($field, $value): self
    {
        $new = clone $this;
        $new->lastField = $field;
        $new->lastValue = $value;
        return $new;
    }

    public function withPageSize(int $pageSize): self
    {
        $new = clone $this;
        $new->pageSize = $pageSize;
        return $new;
    }
}
