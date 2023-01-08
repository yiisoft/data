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
use Yiisoft\Data\Reader\Filter\FilterHandlerInterface;
use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Reader\Iterable\Handler\All;
use Yiisoft\Data\Reader\Iterable\Handler\Any;
use Yiisoft\Data\Reader\Iterable\Handler\Between;
use Yiisoft\Data\Reader\Iterable\Handler\Equals;
use Yiisoft\Data\Reader\Iterable\Handler\EqualsEmpty;
use Yiisoft\Data\Reader\Iterable\Handler\EqualsNull;
use Yiisoft\Data\Reader\Iterable\Handler\GreaterThan;
use Yiisoft\Data\Reader\Iterable\Handler\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Iterable\Handler\In;
use Yiisoft\Data\Reader\Iterable\Handler\IterableHandlerInterface;
use Yiisoft\Data\Reader\Iterable\Handler\LessThan;
use Yiisoft\Data\Reader\Iterable\Handler\LessThanOrEqual;
use Yiisoft\Data\Reader\Iterable\Handler\Like;
use Yiisoft\Data\Reader\Iterable\Handler\Not;
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
 * @template TValue as array|object
 *
 * @implements DataReaderInterface<TKey, TValue>
 */
class IterableDataReader implements DataReaderInterface
{
    private ?Sort $sort = null;
    private ?FilterInterface $filter = null;
    private int $limit = 0;
    private int $offset = 0;

    /**
     * @psalm-var array<string, IterableHandlerInterface>
     */
    private array $filterProcessors = [];

    /**
     * @psalm-param iterable<TKey, TValue> $data
     */
    public function __construct(protected iterable $data)
    {
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

    public function withFilterProcessors(FilterHandlerInterface ...$filterProcessors): static
    {
        $new = clone $this;
        $processors = [];

        foreach ($filterProcessors as $filterProcessor) {
            if ($filterProcessor instanceof IterableHandlerInterface) {
                $processors[$filterProcessor->getOperator()] = $filterProcessor;
            }
        }

        $new->filterProcessors = array_merge($this->filterProcessors, $processors);
        return $new;
    }

    public function withFilter(?FilterInterface $filter): static
    {
        $new = clone $this;
        $new->filter = $filter;
        return $new;
    }

    public function withLimit(int $limit): static
    {
        if ($limit < 0) {
            throw new InvalidArgumentException('The limit must not be less than 0.');
        }

        $new = clone $this;
        $new->limit = $limit;
        return $new;
    }

    public function withOffset(int $offset): static
    {
        $new = clone $this;
        $new->offset = $offset;
        return $new;
    }

    public function withSort(?Sort $sort): static
    {
        $new = clone $this;
        $new->sort = $sort;
        return $new;
    }

    /**
     * @psalm-return Generator<array-key, TValue, mixed, void>
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

    /**
     * @psalm-return array<TKey, TValue>
     */
    public function read(): array
    {
        $data = [];
        $skipped = 0;
        $filter = $this->filter?->toArray();
        $sortedData = $this->sort === null ? $this->data : $this->sortItems($this->data, $this->sort);

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

    public function readOne(): array|object|null
    {
        return $this
            ->withLimit(1)
            ->getIterator()
            ->current();
    }

    protected function matchFilter(array|object $item, array $filter): bool
    {
        $operation = array_shift($filter);
        $arguments = $filter;

        if (!is_string($operation)) {
            throw new RuntimeException(
                sprintf(
                    'The operator should be string. The %s is received.',
                    FilterDataValidationHelper::getValueType($operation),
                )
            );
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
     * @return array The sorted items.
     *
     * @psalm-param iterable<TKey, TValue> $items
     * @psalm-return iterable<TKey, TValue>
     */
    private function sortItems(iterable $items, Sort $sort): iterable
    {
        $criteria = $sort->getCriteria();

        if ($criteria !== []) {
            $items = $this->iterableToArray($items);
            uasort(
                $items,
                static function (array|object $itemA, array|object $itemB) use ($criteria) {
                    foreach ($criteria as $key => $order) {
                        /** @var mixed */
                        $valueA = ArrayHelper::getValue($itemA, $key);
                        /** @var mixed */
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

    /**
     * @param iterable<TKey, TValue> $iterable
     *
     * @return array<TKey, TValue>
     */
    private function iterableToArray(iterable $iterable): array
    {
        return $iterable instanceof Traversable ? iterator_to_array($iterable, true) : $iterable;
    }
}
