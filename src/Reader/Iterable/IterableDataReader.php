<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable;

use Traversable;
use Yiisoft\Arrays\ArraySorter;
use Yiisoft\Data\Reader\CountableDataInterface;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Processor\All;
use Yiisoft\Data\Reader\Iterable\Processor\Any;
use Yiisoft\Data\Reader\Iterable\Processor\Equals;
use Yiisoft\Data\Reader\Iterable\Processor\GreaterThan;
use Yiisoft\Data\Reader\Iterable\Processor\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Iterable\Processor\In;
use Yiisoft\Data\Reader\Iterable\Processor\LessThan;
use Yiisoft\Data\Reader\Iterable\Processor\LessThanOrEqual;
use Yiisoft\Data\Reader\Iterable\Processor\Like;
use Yiisoft\Data\Reader\Iterable\Processor\Not;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;
use Yiisoft\Data\Reader\Iterable\Processor\IterableProcessorInterface;
use Yiisoft\Data\Reader\FilterableDataInterface;
use Yiisoft\Data\Reader\OffsetableDataInterface;
use Yiisoft\Data\Reader\Sort;
use Yiisoft\Data\Reader\SortableDataInterface;

class IterableDataReader implements
    DataReaderInterface,
    SortableDataInterface,
    FilterableDataInterface,
    OffsetableDataInterface,
    CountableDataInterface
{
    protected iterable $data;
    private ?Sort $sort = null;
    private ?FilterInterface $filter = null;
    private int $limit = self::DEFAULT_LIMIT;
    private int $offset = 0;

    private array $filterProcessors = [];

    public function __construct(iterable $data)
    {
        $this->data = $data;
        $this->filterProcessors = $this->withFilterProcessors(
            new All(),
            new Any(),
            new Equals(),
            new GreaterThan(),
            new GreaterThanOrEqual(),
            new In(),
            new LessThan(),
            new LessThanOrEqual(),
            new Like(),
            new Not()
        )->filterProcessors;
    }

    public function withSort(?Sort $sort): self
    {
        $new = clone $this;
        $new->sort = $sort;
        return $new;
    }

    public function getSort(): ?Sort
    {
        return $this->sort;
    }

    /**
     * Sorts data items according to the given sort definition.
     * @param iterable $items the items to be sorted
     * @param Sort $sort the sort definition
     * @return array the sorted items
     */
    private function sortItems(iterable $items, Sort $sort): iterable
    {
        $criteria = $sort->getCriteria();
        if ($criteria !== []) {
            $items = $this->iterableToArray($items);
            ArraySorter::multisort($items, array_keys($criteria), array_values($criteria));
        }

        return $items;
    }

    protected function matchFilter(array $item, array $filter): bool
    {
        $operation = array_shift($filter);
        $arguments = $filter;

        $processor = $this->filterProcessors[$operation] ?? null;
        if ($processor === null) {
            throw new \RuntimeException(sprintf('Operation "%s" is not supported', $operation));
        }
        /* @var $processor IterableProcessorInterface */
        return $processor->match($item, $arguments, $this->filterProcessors);
    }

    public function withFilter(?FilterInterface $filter): self
    {
        $new = clone $this;
        $new->filter = $filter;
        return $new;
    }

    public function withLimit(int $limit): self
    {
        $new = clone $this;
        $new->limit = $limit;
        return $new;
    }

    public function read(): iterable
    {
        $filter = null;
        if ($this->filter !== null) {
            $filter = $this->filter->toArray();
        }

        $data = [];
        $skipped = 0;

        $sortedData = $this->sort === null
            ? $this->data
            : $this->sortItems($this->data, $this->sort);

        foreach ($sortedData as $item) {
            // do not return more than limit items
            if (count($data) === $this->limit) {
                break;
            }

            // skip offset items
            if ($skipped < $this->offset) {
                $skipped++;
                continue;
            }

            // filter items
            if ($filter === null || $this->matchFilter($item, $filter)) {
                $data[] = $item;
            }
        }

        return $data;
    }

    public function withOffset(int $offset): self
    {
        $new = clone $this;
        $new->offset = $offset;
        return $new;
    }

    public function count(): int
    {
        return count($this->read());
    }

    private function iterableToArray(iterable $iterable): array
    {
        return $iterable instanceof Traversable ? iterator_to_array($iterable, true) : (array)$iterable;
    }

    public function withFilterProcessors(FilterProcessorInterface ... $filterProcessors): self
    {
        $new = clone $this;
        $processors = [];
        foreach ($filterProcessors as $filterProcessor) {
            if ($filterProcessor instanceof IterableProcessorInterface) {
                $processors[$filterProcessor->getOperator()] = $filterProcessor;
            }
        }
        $new->filterProcessors = array_merge($this->filterProcessors, $processors);
        return $new;
    }
}
