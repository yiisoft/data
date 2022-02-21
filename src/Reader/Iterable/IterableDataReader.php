<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use Traversable;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Filter\FilterInterface;
use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;
use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Reader\Iterable\Processor\All;
use Yiisoft\Data\Reader\Iterable\Processor\Any;
use Yiisoft\Data\Reader\Iterable\Processor\Between;
use Yiisoft\Data\Reader\Iterable\Processor\Equals;
use Yiisoft\Data\Reader\Iterable\Processor\EqualsEmpty;
use Yiisoft\Data\Reader\Iterable\Processor\EqualsNull;
use Yiisoft\Data\Reader\Iterable\Processor\GreaterThan;
use Yiisoft\Data\Reader\Iterable\Processor\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Iterable\Processor\In;
use Yiisoft\Data\Reader\Iterable\Processor\IterableProcessorInterface;
use Yiisoft\Data\Reader\Iterable\Processor\LessThan;
use Yiisoft\Data\Reader\Iterable\Processor\LessThanOrEqual;
use Yiisoft\Data\Reader\Iterable\Processor\Like;
use Yiisoft\Data\Reader\Iterable\Processor\Not;
use Yiisoft\Data\Reader\Sort;

use function array_merge;
use function array_shift;
use function count;
use function is_string;
use function iterator_to_array;
use function sprintf;
use function uasort;

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
    private int $limit = 0;
    private int $offset = 0;

    /**
     * @psalm-var array<string, FilterProcessorInterface&IterableProcessorInterface>
     */
    private array $filterProcessors = [];

    /**
     * @psalm-param iterable<TKey, TValue> $data
     */
    public function __construct(iterable $data)
    {
        $this->data = $data;
        $this->filterProcessors = $this->withFilterProcessors(
            new All(),
            new Any(),
            new Between(),
            new Equals(),
            new EqualsEmpty(),
            new EqualsNull(),
            new GreaterThan(),
            new GreaterThanOrEqual(),
            new In(),
            new LessThan(),
            new LessThanOrEqual(),
            new Like(),
            new Not()
        )->filterProcessors;
    }

    public function withFilterProcessors(FilterProcessorInterface ...$filterProcessors): self
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

    public function withFilter(?FilterInterface $filter): self
    {
        $new = clone $this;
        $new->filter = $filter;
        return $new;
    }

    public function withLimit(int $limit): self
    {
        if ($limit < 0) {
            throw new InvalidArgumentException('The limit must not be less than 0.');
        }

        $new = clone $this;
        $new->limit = $limit;
        return $new;
    }

    public function withOffset(int $offset): self
    {
        $new = clone $this;
        $new->offset = $offset;
        return $new;
    }

    public function withSort(?Sort $sort): self
    {
        $new = clone $this;
        $new->sort = $sort;
        return $new;
    }

    /**
     * @psalm-return Generator<TValue>
     */
    public function getIterator(): Generator
    {
        yield from $this->read();
    }

    public function getSort(): ?Sort
    {
        return $this->sort;
    }

    public function count(): int
    {
        return count($this->read());
    }

    public function read(): array
    {
        $data = [];
        $skipped = 0;
        $filter = $this->filter === null ? null : $this->filter->toArray();
        $sortedData = $this->sort === null ? $this->data : $this->sortItems($this->data, $this->sort);

        /**
         * @var int|string $key
         * @var array $item
         */
        foreach ($sortedData as $key => $item) {
            // Do not return more than limit items.
            if ($this->limit > 0 && count($data) === $this->limit) {
                break;
            }

            // Skip offset items.
            if ($skipped < $this->offset) {
                ++$skipped;
                continue;
            }

            // Filter items.
            if ($filter === null || $this->matchFilter($item, $filter)) {
                $data[$key] = $item;
            }
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function readOne()
    {
        return $this->withLimit(1)->getIterator()->current();
    }

    protected function matchFilter(array $item, array $filter): bool
    {
        $operation = array_shift($filter);
        $arguments = $filter;

        if (!is_string($operation)) {
            throw new RuntimeException(sprintf(
                'The operator should be string. The %s is received.',
                FilterDataValidationHelper::getValueType($operation),
            ));
        }

        if ($operation === '') {
            throw new RuntimeException('The operator string cannot be empty.');
        }

        $processor = $this->filterProcessors[$operation] ?? null;

        if ($processor === null) {
            throw new RuntimeException(sprintf('Operation "%s" is not supported.', $operation));
        }

        return $processor->match($item, $arguments, $this->filterProcessors);
    }

    /**
     * Sorts data items according to the given sort definition.
     *
     * @param iterable $items The items to be sorted.
     * @param Sort $sort The sort definition.
     *
     * @return iterable The sorted items.
     */
    private function sortItems(iterable $items, Sort $sort): iterable
    {
        $criteria = $sort->getCriteria();

        if ($criteria !== []) {
            $items = $this->iterableToArray($items);
            uasort(
                $items,
                /**
                 * @param mixed $itemA
                 * @param mixed $itemB
                 */
                static function ($itemA, $itemB) use ($criteria) {
                    foreach ($criteria as $key => $order) {
                        /** @psalm-suppress MixedArgument, MixedAssignment */
                        $valueA = ArrayHelper::getValue($itemA, $key);
                        /** @psalm-suppress MixedArgument, MixedAssignment */
                        $valueB = ArrayHelper::getValue($itemB, $key);

                        if ($valueB === $valueA) {
                            continue;
                        }

                        return ($valueA > $valueB xor $order === SORT_DESC) ? 1 : -1;
                    }

                    return 0;
                }
            );
        }

        return $items;
    }

    private function iterableToArray(iterable $iterable): array
    {
        /** @psalm-suppress RedundantCast */
        return $iterable instanceof Traversable ? iterator_to_array($iterable, true) : (array) $iterable;
    }
}
