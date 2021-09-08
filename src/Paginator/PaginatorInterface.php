<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

use Yiisoft\Data\Reader\Sort;

/**
 * @template TKey as array-key
 * @template TValue
 */
interface PaginatorInterface
{
    public const DEFAULT_PAGE_SIZE = 10;

    /**
     * @psalm-return iterable<TKey, TValue>
     */
    public function read(): iterable;

    /**
     * Check that Paginator has more than one page
     */
    public function isRequired(): bool;

    public function isOnLastPage(): bool;

    public function isOnFirstPage(): bool;

    public function getNextPageToken(): ?string;

    public function getPreviousPageToken(): ?string;

    /**
     * @return static
     *
     * @psalm-mutation-free
     */
    public function withNextPageToken(?string $token): self;

    /**
     * @return static
     *
     * @psalm-mutation-free
     */
    public function withPreviousPageToken(?string $token): self;

    /**
     * @return static
     *
     * @psalm-mutation-free
     */
    public function withPageSize(int $pageSize): self;

    public function getPageSize(): int;

    public function getCurrentPageSize(): int;

    /**
     * @return Sort|null Current sort object or null if no sorting is used.
     */
    public function getSort(): ?Sort;
}
