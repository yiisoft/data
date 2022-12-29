<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable;

use Traversable;
use Yiisoft\Data\Processor\DataProcessorInterface;
use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Iterable\Processor\IterableProcessorInterface;
use Yiisoft\Data\Reader\Sort;

use function count;

/**
 * @template TKey as array-key
 * @template TValue as array|object
 *
 * @implements DataProcessorInterface<TKey, TValue>
 */
final class IterableDataProcessor implements DataProcessorInterface
{
    /**
     * @psalm-var array<string, IterableProcessorInterface>
     */
    private array $filterProcessors;

    /**
     * @param IterableProcessorInterface[] $filterProcessors
     */
    public function __construct(
        private ?FilterInterface $filter = null,
        private ?Sort $sort = null,
        ?array $filterProcessors = null,
    ) {
        $this->filterProcessors = IterableHelper::prepareFilterProcessors(
            $filterProcessors ?? IterableHelper::getBuiltInFilterProcessors()
        );
    }

    public function withFilter(FilterInterface ...$filters): self
    {
        $new = clone $this;

        $countFilters = count($filters);

        if ($countFilters === 0) {
            $new->filter = null;
        } else {
            $new->filter = $countFilters === 1
                ? reset($filters)
                : new All(...$filters);
        }

        return $new;
    }

    public function withSort(?Sort $sort): self
    {
        $new = clone $this;
        $new->sort = $sort;
        return $new;
    }

    public function process(iterable $items): iterable
    {
        $items = $items instanceof Traversable ? iterator_to_array($items) : $items;

        if ($this->filter === null) {
            return IterableHelper::sortItems($items, $this->sort);
        }

        $filter = $this->filter->toArray();

        $result = array_filter(
            $items,
            fn($item) => IterableHelper::matchFilter($item, $filter, $this->filterProcessors),
        );

        return IterableHelper::sortItems($result, $this->sort);
    }
}
