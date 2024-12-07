<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

use LogicException;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;
use Yiisoft\Data\Reader\Sort;

/**
 * Paginator interface defines a common pagination methods shared by all pagination types.
 *
 * - Each page has string "tokens" for getting to the previous and next page.
 * - Maximum number of items per page could be set.
 *
 * @template TKey as array-key
 * @template TValue as array|object
 *
 * @extends ReadableDataInterface<TKey, TValue>
 */
interface PaginatorInterface extends ReadableDataInterface
{
    /**
     * Page size that is used in case it is not set explicitly.
     *
     * @psalm-suppress MissingClassConstType
     */
    public const DEFAULT_PAGE_SIZE = 10;

    /**
     * Get a new instance with page token.
     *
     * @param PageToken|null $token Page token. `Null` if current page is first.
     *
     * @throws PaginatorException If page token is incorrect.
     *
     * @return static New instance.
     *
     * @see PageToken
     */
    public function withToken(?PageToken $token): static;

    /**
     * Get a new instance with a page size set.
     *
     * @param int $pageSize Maximum number of items per page.
     *
     * @throws PaginatorException If page size is incorrect.
     *
     * @return static New instance.
     */
    public function withPageSize(int $pageSize): static;

    /**
     * @return PageToken|null Current page token or `null` if not set.
     */
    public function getToken(): ?PageToken;

    /**
     * Get token for the next page.
     *
     * @return PageToken|null Page token for the next page. `null` if the current page is last.
     */
    public function getNextToken(): ?PageToken;

    /**
     * Get token for the previous page.
     *
     * @return PageToken|null Page token for the previous page. `null` if current page is first.
     */
    public function getPreviousToken(): ?PageToken;

    /**
     * Get the maximum number of items per page.
     *
     * Note that there could be less current page items.
     *
     * @see getCurrentPageSize()
     *
     * @return int Page size.
     *
     * @psalm-return positive-int
     */
    public function getPageSize(): int;

    /**
     * Get number of items at the current page.
     *
     * Note that it is an actual number of items, not the limit.
     *
     * @see getPageSize()
     *
     * @return int Current page size.
     *
     * @psalm-return non-negative-int
     */
    public function getCurrentPageSize(): int;

    /**
     * @return bool Whether changing sorting via {@see withSorting()} is supported.
     */
    public function isSortable(): bool;

    /**
     * Get a new instance with a sorting set.
     *
     * @param Sort|null $sort Sorting criteria or null for no sorting.
     *
     * @throws LogicException When changing sorting isn't supported.
     * @return static New instance.
     */
    public function withSort(?Sort $sort): static;

    /**
     * Get current sort object.
     *
     * @return Sort|null Current sort object or null if no sorting is used.
     */
    public function getSort(): ?Sort;

    /**
     * @return bool Whether changing filter via {@see withFilter()} is supported.
     */
    public function isFilterable(): bool;

    /**
     * Returns new instance with data reading criteria set.
     *
     * @param FilterInterface $filter Data reading criteria.
     *
     * @throws LogicException When changing filter isn't supported.
     * @return static New instance.
     */
    public function withFilter(FilterInterface $filter): static;

    /**
     * Get an iterator that could be used to read currently active page items.
     *
     * @throws PageNotFoundException If the page specified isn't found.
     *
     * @return iterable Iterator with items for the current page.
     * @psalm-return iterable<TKey, TValue>
     */
    public function read(): iterable;

    /**
     * Get whether the current page is the last one.
     *
     * @throws PageNotFoundException If the page specified isn't found.
     *
     * @return bool Whether the current page is the last one.
     */
    public function isOnLastPage(): bool;

    /**
     * Get whether the current page is the first one.
     *
     * @return bool Whether the current page is the first one.
     */
    public function isOnFirstPage(): bool;

    /**
     * Check that there is more than a single page so pagination is necessary.
     *
     * @return bool Whether pagination is required.
     */
    public function isPaginationRequired(): bool;
}
