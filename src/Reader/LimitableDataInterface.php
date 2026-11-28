<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use InvalidArgumentException;

/**
 * Data that could be limited.
 *
 * @template TKey of array-key = array-key
 * @template TValue of array|object = array|object
 *
 * @extends ReadableDataInterface<TKey, TValue>
 */
interface LimitableDataInterface extends ReadableDataInterface
{
    /**
     * Get a new instance with the limit set.
     *
     * @param non-negative-int|null $limit Limit. `null` means no limit.
     *
     * @throws InvalidArgumentException If the limit is less than zero.
     *
     * @return static New instance.
     */
    public function withLimit(?int $limit): static;

    /**
     * Get current limit.
     *
     * @return non-negative-int|null Limit. `null` means no limit.
     */
    public function getLimit(): ?int;
}
