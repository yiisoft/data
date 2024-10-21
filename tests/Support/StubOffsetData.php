<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Support;

use Yiisoft\Data\Reader\CountableDataInterface;
use Yiisoft\Data\Reader\LimitableDataInterface;
use Yiisoft\Data\Reader\OffsetableDataInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;

final class StubOffsetData implements
    ReadableDataInterface,
    OffsetableDataInterface,
    CountableDataInterface,
    LimitableDataInterface
{
    /**
     * @var int|null
     * @psalm-var non-negative-int
     */
    private ?int $limit = null;

    public function read(): iterable
    {
        return [];
    }

    public function readOne(): array|object|null
    {
        return null;
    }

    public function count(): int
    {
        return 0;
    }

    public function withLimit(?int $limit): static
    {
        $new = clone $this;
        $new->limit = $limit;
        return $new;
    }

    public function withOffset(int $offset): static
    {
        return $this;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return 0;
    }
}
