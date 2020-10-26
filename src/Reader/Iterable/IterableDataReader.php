<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable;

use Generator;
use Traversable;
use Yiisoft\Arrays\ArraySorter;
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
use Yiisoft\Data\Reader\Sort;

/**
 * @template TKey as array-key
 * @template TValue
 *
 * @implements DataReaderInterface<TKey, TValue>
 */
class IterableDataReader implements DataReaderInterface
{
    /**
     * @psalm-var iterable<TKey, TValue>
     */
    protected iterable $data;
    private ?Sort $sort = null;
    private ?FilterInterface $filter = null;
    private ?int $limit = null;
    private int $offset = 0;

    private array $filterProcessors = [];

    /**
     * psalm-param iterable<TKey, TValue> $data
     */
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

    /**
     * @psalm-mutation-free
     */
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
     * @return iterable the sorted items
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

    /**
     * @psalm-mutation-free
     */
    public function withFilter(?FilterInterface $filter): self
    {
        $new = clone $this;
        $new->filter = $filter;
        return $new;
    }

    /**
     * @psalm-mutation-free
     */
    public function withLimit(int $limit): self
    {
        if ($limit < 0) {
            throw new \InvalidArgumentException('$limit must not be less than 0.');
        }
        $new = clone $this;
        $new->limit = $limit;
        return $new;
    }

    public function read(): array
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
            if ($this->limit !== null && count($data) === $this->limit) {
                break;
            }

            // skip offset items
            if ($skipped < $this->offset) {
                ++$skipped;
                continue;
            }

            // filter items
            if ($filter === null || $this->matchFilter($item, $filter)) {
                $data[] = $item;
            }
        }

        return $data;
    }

    public function readOne()
    {
        return $this->withLimit(1)->getIterator()->current();
    }

    /**
     * @psalm-return Generator<TValue>
     */
    public function getIterator(): Generator
    {
        yield from $this->read();
    }

    /**
     * @psalm-mutation-free
     */
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

    /**
     * @psalm-mutation-free
     */
    public function withFilterProcessors(FilterProcessorInterface ...$filterProcessors): self
    {
        $new = clone $this;
        $processors = [];
        foreach ($filterProcessors as $filterProcessor) {
            if ($filterProcessor instanceof IterableProcessorInterface) {
                /** @psalm-suppress ImpureMethodCall */
                $processors[$filterProcessor->getOperator()] = $filterProcessor;
            }
        }
        $new->filterProcessors = array_merge($this->filterProcessors, $processors);
        return $new;
    }
}
