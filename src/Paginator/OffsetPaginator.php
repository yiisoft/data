<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

use Yiisoft\Data\Reader\CountableDataInterface;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\OffsetableDataInterface;

final class OffsetPaginator implements OffsetPaginatorInterface
{
    /** @var OffsetableDataInterface|DataReaderInterface|CountableDataInterface */
    private DataReaderInterface $dataReader;
    private int $currentPage = 1;
    private int $pageSize = self::DEFAULT_PAGE_SIZE;
    /** Reader cache against repeated scans */
    private ?array $readCache = null;
    /** Cached value */
    private ?int $totalItemsCount = null;

    public function __construct(DataReaderInterface $dataReader)
    {
        if (!$dataReader instanceof OffsetableDataInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Data reader should implement %s in order to be used with offset paginator',
                    OffsetableDataInterface::class
                )
            );
        }

        if (!$dataReader instanceof CountableDataInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Data reader should implement %s in order to be used with offset paginator',
                    CountableDataInterface::class
                )
            );
        }

        $this->dataReader = $dataReader;
    }

    public function __clone()
    {
        $this->readCache = null;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function withCurrentPage(int $page): self
    {
        if ($page < 1) {
            throw new PaginatorException('Current page should be at least 1');
        }
        $new = clone $this;
        $new->currentPage = $page;
        return $new;
    }

    public function withPageSize(int $size): self
    {
        if ($size < 1) {
            throw new PaginatorException('Page size should be at least 1');
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
        if ($this->currentPage > $this->getTotalPages()) {
            throw new PaginatorException('Page not found');
        }
        return $this->currentPage === $this->getTotalPages();
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotalItems() / $this->pageSize);
    }

    public function getOffset(): int
    {
        return $this->pageSize * ($this->currentPage - 1);
    }

    public function read(): iterable
    {
        if ($this->readCache !== null) {
            return $this->readCache;
        }
        if ($this->currentPage > $this->getTotalPages()) {
            throw new PaginatorException('Page not found');
        }
        /** @var OffsetableDataInterface|DataReaderInterface|CountableDataInterface $reader */
        $reader = $this->dataReader->withLimit($this->pageSize)->withOffset($this->getOffset());
        $iterable = $reader->read();
        if (is_array($iterable)) {
            $this->readCache = $iterable;
        } else {
            $this->readCache = [];
            foreach ($iterable as $item) {
                $this->readCache[] = $item;
            }
        }
        return $this->readCache;
    }

    public function getNextPageToken(): ?string
    {
        return $this->isOnLastPage() ? null : (string) ($this->currentPage + 1);
    }

    public function getPreviousPageToken(): ?string
    {
        return $this->isOnFirstPage() ? null : (string) ($this->currentPage - 1);
    }

    public function withNextPageToken(?string $token): self
    {
        return $this->withCurrentPage((int)$token);
    }

    public function withPreviousPageToken(?string $token): self
    {
        return $this->withCurrentPage((int)$token);
    }

    public function getCurrentPageSize(): int
    {
        if ($this->readCache !== null) {
            return count($this->readCache);
        }
        $pages = $this->getTotalPages();
        if ($pages === 1) {
            return $this->getTotalItems();
        }
        if ($this->currentPage >= $pages) {
            return $this->getTotalItems() - $this->getOffset();
        }
        return $this->pageSize;
    }

    public function getTotalItems(): int
    {
        if ($this->totalItemsCount === null) {
            $this->totalItemsCount = $this->dataReader->count();
        }
        return $this->totalItemsCount;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }
}
