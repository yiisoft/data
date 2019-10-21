<?php
declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

use Yiisoft\Data\Reader\CountableDataInterface;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\OffsetableDataInterface;

/**
 * OffsetPaginator
 */
final class OffsetPaginator implements PaginatorInterface
{
    /**
     * @var OffsetableDataInterface|DataReaderInterface|CountableDataInterface
     */
    private $dataReader;

    private $currentPage = 1;
    private $pageSize = self::DEFAULT_PAGE_SIZE;

    /**
     * @var array|null Reader cache against repeated scans.
     *
     * @see initializeInternal()
     */
    private $readCache;
    /**
     * @var int|null Total count cache against repeated scans.
     *
     * See more {@see initializeInternal()}.
     */
    private $totalCountCache;

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

    public function withCurrentPage(int $page)
    {
        if ($page < 1) {
            throw new \InvalidArgumentException('Current page should be at least 1');
        }

        $new = clone $this;
        $new->currentPage = $page;
        return $new;
    }

    public function withPageSize(int $size)
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
        $totalCount = $this->totalCountCache;
        if ($totalCount === null) {
            $totalCount = $this->dataReader->count();
        }
        return (int) ceil($totalCount / $this->pageSize);
    }

    private function getOffset(): int
    {
        return $this->pageSize * ($this->currentPage - 1);
    }

    public function read(): iterable
    {
        if($this->readCache !== null) {
            return $this->readCache;
        }
        $reader = $this->dataReader->withLimit($this->pageSize)->withOffset($this->getOffset());
        yield from $reader->read();
    }

    public function getNextPageToken(): ?string
    {
        return $this->isOnLastPage() ? null : (string) ($this->currentPage + 1);
    }

    public function getPreviousPageToken(): ?string
    {
        return $this->isOnFirstPage() ? null : (string) ($this->currentPage - 1);
    }

    public function withNextPageToken(?string $token)
    {
        return $this->withCurrentPage((int)$token);
    }

    public function withPreviousPageToken(?string $token)
    {
        return $this->withCurrentPage((int)$token);
    }

    public function getCurrentPageSize(): int
    {
        $this->initializeInternal();
        return count($this->readCache);
    }

    public function __clone()
    {
        $this->readCache = null;
        $this->totalCountCache = null;
    }

    private function initializeInternal(): void
    {
        if ($this->readCache !== null) {
            return;
        }
        $cache = [];
        foreach ($this->read() as $value) {
            $cache[] = $value;
        }
        $this->readCache = $cache;
    }
}
