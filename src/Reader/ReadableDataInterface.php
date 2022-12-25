<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use InvalidArgumentException;

/**
 * @template TKey as array-key
 * @template TValue as array|object
 */
interface ReadableDataInterface
{
    /**
     * @param int $limit A limit of 0 means "no limit".
     *
     * @throws InvalidArgumentException if limit less than 0.
     *
     * @return static
     */
    public function withLimit(int $limit): static;

    /**
     * @psalm-return iterable<TKey, TValue>
     */
    public function read(): iterable;

    /**
     * @psalm-return TValue|null
     */
    public function readOne(): array|object|null;
}
