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
     * Get a new instance with the limit set.
     *
     * @param ?int $limit Limit. `null` means no limit.
     *
     * @throws InvalidArgumentException If the limit is less than zero.
     *
     * @return static New instance.
     * @psalm-return $this
     */
    public function withLimit(?int $limit): static;

    /**
     * Get current limit.
     *
     * @return ?int Limit. `null` means no limit.
     */
    public function getLimit(): ?int;
}
