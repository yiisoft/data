<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use InvalidArgumentException;

/**
 * Data that could be limited.
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
}
