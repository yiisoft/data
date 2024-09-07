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
        return $this;
    }

    public function withOffset(int $offset): static
    {
        return $this;
    }

    public function getLimit(): int
    {
        return 0;
    }

    public function getOffset(): int
    {
        return 0;
    }
}
