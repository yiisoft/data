<?php

namespace Yiisoft\Data\Paginatior;

use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\OffsetableDataInterface;

/**
 * OffsetPaginator
 */
class OffsetPaginator
{
    /**
     * @var DataReaderInterface|OffsetableDataInterface
     */
    private $dataReader;

    private $currentPage = 1;
    private $pageSize = 10;

    public function __construct(DataReaderInterface $dataReader)
    {
        if (!$dataReader instanceof OffsetableDataInterface) {
            throw new \InvalidArgumentException('Data reader should implement OffsetableDataInterface in order to be used with offset paginator');
        }

        $this->dataReader = $dataReader;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function setCurrentPage(int $page): void
    {
        $this->currentPage = $page;
    }

    public function setPageSize(int $size): void
    {
        $this->pageSize = $size;
    }

    public function isLastPage(): bool
    {

    }

    private function getOffset(): int
    {
        return $this->pageSize * ($this->currentPage - 1);
    }

    public function read(): iterable
    {
        $reader = $this->dataReader->withLimit($this->pageSize)->withOffest($this->getOffset());
        yield from $reader->read();
    }
}
