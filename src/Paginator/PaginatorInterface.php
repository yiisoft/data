<?php
declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

interface PaginatorInterface
{
    public const DEFAULT_PAGE_SIZE = 10;

    public function read(): iterable;
    public function isOnLastPage(): bool;
    public function isOnFirstPage(): bool;
    public function getNextPageToken(): ?string;
    public function getPreviousPageToken(): ?string;
    /**
     * @return static
     */
    public function withNextPageToken(?string $token);
    /**
     * @return static
     */
    public function withPreviousPageToken(?string $token);
    /**
     * @return static
     */
    public function withPageSize(int $limit);
    public function getCurrentPageSize(): int;
}
