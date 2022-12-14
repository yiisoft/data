<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

/**
 * @template TKey as array-key
 * @template TValue
 */
interface PaginatorInterface
{
    public const DEFAULT_PAGE_SIZE = 10;

    public function withNextPageToken(?string $token): self;

    public function withPreviousPageToken(?string $token): self;

    public function withPageSize(int $pageSize): self;

    public function getNextPageToken(): ?string;

    public function getPreviousPageToken(): ?string;

    public function getPageSize(): int;

    public function getCurrentPageSize(): int;

    /**
     * @psalm-return iterable<TKey, TValue>
     */
    public function read(): iterable;

    public function isOnLastPage(): bool;

    public function isOnFirstPage(): bool;

    /**
     * Check that Paginator has more than one page.
     */
    public function isRequired(): bool;
}
