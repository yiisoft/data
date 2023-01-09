<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable;

use Generator;
use InvalidArgumentException;
use RuntimeException;
use Traversable;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\FilterHandlerInterface;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\FilterHandler\All;
use Yiisoft\Data\Reader\Iterable\FilterHandler\Any;
use Yiisoft\Data\Reader\Iterable\FilterHandler\Between;
use Yiisoft\Data\Reader\Iterable\FilterHandler\Equals;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsEmpty;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsNull;
use Yiisoft\Data\Reader\Iterable\FilterHandler\GreaterThan;
use Yiisoft\Data\Reader\Iterable\FilterHandler\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Iterable\FilterHandler\In;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LessThan;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LessThanOrEqual;
use Yiisoft\Data\Reader\Iterable\FilterHandler\Like;
use Yiisoft\Data\Reader\Iterable\FilterHandler\Not;
use Yiisoft\Data\Reader\Sort;

use function array_merge;
use function array_shift;
use function count;
use function is_string;
use function iterator_to_array;
use function sprintf;
use function uasort;

/**
 * Iterable data reader takes an iterable data as a source and can:
 *
 * - Limit items read
 * - Skip N items from the beginning
 * - Sort items
 * - Form a filter criteria with {@see FilterInterface}
 * - Post-filter items with {@see IterableFilterHandlerInterface}
 *
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
     * @psalm-var array<string, IterableFilterHandlerInterface>
     */
    private array $iterableFilterHandlers = [];

    /**
     * @param iterable $data Data to iterate.
     * @psalm-param iterable<TKey, TValue> $data
     */
    public function __construct(protected iterable $data)
    {
        $this->iterableFilterHandlers = $this->withFilterHandlers(
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
        )->iterableFilterHandlers;
    }

    public function withFilterHandlers(FilterHandlerInterface ...$iterableFilterHandlers): static
    {
        $new = clone $this;
        $handlers = [];

        foreach ($iterableFilterHandlers as $iterableFilterHandler) {
            if ($iterableFilterHandler instanceof IterableFilterHandlerInterface) {
                $handlers[$iterableFilterHandler->getOperator()] = $iterableFilterHandler;
            }
        }

        $new->iterableFilterHandlers = array_merge($this->iterableFilterHandlers, $handlers);
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
        $filter = $this->filter?->toCriteriaArray();
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

    /**
     * Return whether an item matches iterable filter.
     *
     * @param array|object $item Item to check.
     * @param array $filter Filter.
     *
     * @return bool Whether an item matches iterable filter.
     */
    protected function matchFilter(array|object $item, array $filter): bool
    {
        $operation = array_shift($filter);
        $arguments = $filter;

        if (!is_string($operation)) {
            throw new RuntimeException(
                sprintf(
                    'The operator should be string. The %s is received.',
                    get_debug_type($operation),
                )
            );
        }

        if ($operation === '') {
            throw new RuntimeException('The operator string cannot be empty.');
        }

        $processor = $this->iterableFilterHandlers[$operation] ?? null;

        if ($processor === null) {
            throw new RuntimeException(sprintf('Operation "%s" is not supported.', $operation));
        }

        return $processor->match($item, $arguments, $this->iterableFilterHandlers);
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
     * Convert iterable to array.
     *
     * @param iterable $iterable Iterable to convert.
     * @psalm-param iterable<TKey, TValue> $iterable
     *
     * @return array Resulting array.
     * @psalm-return array<TKey, TValue>
     */
    private function iterableToArray(iterable $iterable): array
    {
        return $iterable instanceof Traversable ? iterator_to_array($iterable, true) : $iterable;
    }
}
