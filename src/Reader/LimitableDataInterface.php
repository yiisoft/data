<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use InvalidArgumentException;

/**
 * Data that could be limited.
 *
 * @template TKey as array-key
 * @template TValue as array|object
 *
 * @extends ReadableDataInterface<TKey, TValue>
 */
interface LimitableDataInterface extends ReadableDataInterface
{
    /**
     * Get a new instance with the limit set.
     *
     * @param int|null $limit Limit. `null` means no limit.
     *
     * @throws InvalidArgumentException If the limit is less than zero.
     *
     * @return static New instance.
     *
     * @psalm-param non-negative-int|null $limit
     * @psalm-return $this
     */
    public function withLimit(?int $limit): static;

    /**
     * Get current limit.
     *
     * @return int|null Limit. `null` means no limit.
     *
     * @psalm-return non-negative-int|null
     */
    public function getLimit(): ?int;
}
