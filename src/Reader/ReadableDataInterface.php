<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use InvalidArgumentException;

/**
 * @template TKey as array-key
 * @template TValue
 */
interface ReadableDataInterface
{
    /**
     * @param int $limit A limit of 0 means "no limit".
     *
     * @throws InvalidArgumentException if limit less than 0.
     */
    public function withLimit(int $limit): self;

    /**
     * @psalm-return iterable<TKey, TValue>
     */
    public function read(): iterable;

    /**
     * @psalm-return TValue
     */
    public function readOne();
}
