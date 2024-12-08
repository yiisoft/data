<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

/**
 * Data that could be read from Nth item by skipping items from the beginning.
 *
 * @template TKey as array-key
 * @template TValue as array|object
 *
 * @extends ReadableDataInterface<TKey, TValue>
 */
interface OffsetableDataInterface extends ReadableDataInterface
{
    /**
     * Get a new instance with an offset set.
     *
     * @param int $offset Offset.
     *
     * @return $this New instance.
     * @psalm-return $this
     */
    public function withOffset(int $offset): static;

    /**
     * Get current offset.
     *
     * @return int Offset.
     */
    public function getOffset(): int;
}
