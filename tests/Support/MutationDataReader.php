<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Support;

use Closure;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\FilterHandlerInterface;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
use Yiisoft\Data\Reader\LimitableDataInterface;
use Yiisoft\Data\Reader\ReadableDataInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\SortableDataInterface;

final class MutationDataReader implements
    ReadableDataInterface,
    FilterableDataInterface,
    SortableDataInterface,
    LimitableDataInterface
{
    public function __construct(
        private IterableDataReader $decorated,
        private Closure $mutation,
    ) {
    }

    public function withFilter(FilterInterface $filter): static
    {
        $new = clone $this;
        $new->decorated = $this->decorated->withFilter($filter);
        return $new;
    }

    public function withFilterHandlers(FilterHandlerInterface ...$filterHandlers): static
    {
        $new = clone $this;
        $new->decorated = $this->decorated->withFilterHandlers(...$filterHandlers);
        return $new;
    }

    public function withLimit(?int $limit): static
    {
        $new = clone $this;
        $new->decorated = $this->decorated->withLimit($limit);
        return $new;
    }

    public function read(): iterable
    {
        return array_map($this->mutation, $this->decorated->read());
    }

    public function readOne(): array|object|null
    {
        return call_user_func($this->mutation, $this->decorated->readOne());
    }

    public function withSort(?Sort $sort): static
    {
        $new = clone $this;
        $new->decorated = $this->decorated->withSort($sort);
        return $new;
    }

    public function getSort(): ?Sort
    {
        return $this->decorated->getSort();
    }

    public function getFilter(): ?FilterInterface
    {
        return $this->decorated->getFilter();
    }

    public function getLimit(): ?int
    {
        return $this->decorated->getLimit();
    }
}
