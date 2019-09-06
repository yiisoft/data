<?php

namespace Yiisoft\Data\Paginator;

use Yiisoft\Data\Reader\CountableDataInterface;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\OffsetableDataInterface;

/**
 * OffsetPaginator
 */
final class OffsetPaginator
{
    /**
     * @var OffsetableDataInterface|DataReaderInterface
     */
    private $dataReader;

    private $currentPage = 1;
    private $pageSize = 10;

    public function __construct(DataReaderInterface $dataReader)
    {
        if (!$dataReader instanceof OffsetableDataInterface) {
            throw new \InvalidArgumentException('Data reader should implement OffsetableDataInterface in order to be used with offset paginator');
        }

        if (!$dataReader instanceof CountableDataInterface) {
            throw new \InvalidArgumentException('Data reader should implement CountableDataInterface in order to be used with offset paginator');
        }

        $this->dataReader = $dataReader;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function currentPage(int $page): self
    {
        if ($page < 1) {
            throw new \InvalidArgumentException('Current page should be at least 1');
        }

        $new = clone $this;
        $new->currentPage = $page;
        return $new;
    }

    public function pageSize(int $size): self
    {
        if ($size < 1) {
            throw new \InvalidArgumentException('Page size should be at least 1');
        }

        $new = clone $this;
        $new->pageSize = $size;
        return $new;
    }

    public function isOnFirstPage(): bool
    {
        return $this->currentPage === 1;
    }

    public function isOnLastPage(): bool
    {
        return $this->currentPage === $this->getTotalPages();
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->dataReader->count() / $this->pageSize);
    }

    private function getOffset(): int
    {
        return $this->pageSize * ($this->currentPage - 1);
    }

    public function read(): iterable
    {
        $reader = $this->dataReader->limit($this->pageSize)->offset($this->getOffset());
        yield from $reader->read();
    }
}
