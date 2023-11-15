<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use InvalidArgumentException;

/**
 * @template TKey as array-key
 * @template TValue as array|object
 */
interface LimitableDataInterface
{

    /**
     * Get a new instance with limit set.
     *
     * @param int $limit Limit. 0 means "no limit".
     *
     * @throws InvalidArgumentException If limit is less than 0.
     *
     * @return static New instance.
     * @psalm-return $this
     */
    public function withLimit(int $limit): static;

    /**
     * Get a next item from the data set.
     *
     * @return array|object|null An item or null if there is none.
     * @psalm-return TValue|null
     */
    public function readOne(): array|object|null;
}
