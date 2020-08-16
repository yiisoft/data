<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

use Yiisoft\Data\Reader\CountableDataInterface;
use Yiisoft\Data\Reader\OffsetableDataInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;

final class OffsetPaginator implements PaginatorInterface
{
    /** @var OffsetableDataInterface|ReadableDataInterface|CountableDataInterface */
    private ReadableDataInterface $dataReader;
    private int $currentPage = 1;
    private int $pageSize = self::DEFAULT_PAGE_SIZE;
    private ?ReadableDataInterface $cachedReader = null;

    public function __construct(ReadableDataInterface $dataReader)
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

    public function isRequired(): bool
    {
        return $this->getTotalPages() > 1;
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
        $new->cachedReader = null;
        return $new;
    }

    public function withPageSize(int $size): self
    {
        if ($size < 1) {
            throw new PaginatorException('Page size should be at least 1');
        }
        $new = clone $this;
        $new->pageSize = $size;
        $new->cachedReader = null;
        return $new;
    }

    public function isOnFirstPage(): bool
    {
        return $this->currentPage === 1;
    }

    public function isOnLastPage(): bool
    {
        if ($this->currentPage > $this->getInternalTotalPages()) {
            throw new PaginatorException('Page not found');
        }
        return $this->currentPage === $this->getInternalTotalPages();
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
        if ($this->cachedReader !== null) {
            yield from $this->cachedReader->read();
            return;
        }
        if ($this->currentPage > $this->getInternalTotalPages()) {
            throw new PaginatorException('Page not found');
        }
        /** @var OffsetableDataInterface|ReadableDataInterface|CountableDataInterface $reader */
        $this->cachedReader = $this->dataReader->withLimit($this->pageSize)->withOffset($this->getOffset());
        yield from $this->cachedReader->read();
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
        $pages = $this->getInternalTotalPages();
        if ($pages === 1) {
            return $this->getTotalItems();
        }
        if ($this->currentPage === $pages) {
            return $this->getTotalItems() - $this->getOffset();
        }
        if ($this->currentPage > $pages) {
            throw new PaginatorException('Page not found');
        }
        return $this->pageSize;
    }

    public function getTotalItems(): int
    {
        return $this->dataReader->count();
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    private function getInternalTotalPages(): int
    {
        return max(1, $this->getTotalPages());
    }
}
