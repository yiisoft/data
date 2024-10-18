<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Support;

use Yiisoft\Data\Reader\CountableDataInterface;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\FilterHandlerInterface;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\LimitableDataInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\SortableDataInterface;

final class StubKeysetData implements
    ReadableDataInterface,
    CountableDataInterface,
    LimitableDataInterface,
    FilterableDataInterface,
    SortableDataInterface
{
    /**
     * @var int|null
     * @psalm-param null|non-negative-int
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

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function withFilter(?FilterInterface $filter): static
    {
        return clone $this;
    }

    public function getFilter(): ?FilterInterface
    {
        return null;
    }

    public function withAddedFilterHandlers(FilterHandlerInterface ...$filterHandlers): static
    {
        return clone $this;
    }

    public function withSort(?Sort $sort): static
    {
        return clone $this;
    }

    public function getSort(): ?Sort
    {
        return Sort::only(['id'])->withOrderString('id');
    }
}
