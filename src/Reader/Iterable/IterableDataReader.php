<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable;

use Generator;
use InvalidArgumentException;
use Traversable;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\DataReaderInterface;
use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\FilterHandler\AllHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\AndXHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\NoneHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\OrXHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\BetweenHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsNullHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\GreaterThanHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\GreaterThanOrEqualHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\InHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LessThanHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LessThanOrEqualHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LikeHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\NotHandler;
use Yiisoft\Data\Reader\Iterable\ValueReader\FlatValueReader;
use Yiisoft\Data\Reader\Iterable\ValueReader\ValueReaderInterface;
use Yiisoft\Data\Reader\Sort;

use function array_merge;
use function count;
use function iterator_to_array;
use function uasort;

/**
 * Iterable data reader takes iterable data as a source and can:
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
final class IterableDataReader implements DataReaderInterface
{
    private ?Sort $sort = null;
    private FilterInterface $filter;

    /**
     * @psalm-var non-negative-int|null
     */
    private ?int $limit = null;
    private int $offset = 0;

    /**
     * @psalm-var array<string, IterableFilterHandlerInterface>
     */
    private array $coreFilterHandlers;

    private Context $context;

    /**
     * @param iterable $data Data to iterate.
     * @psalm-param iterable<TKey, TValue> $data
     */
    public function __construct(
        private readonly iterable $data,
        private readonly ValueReaderInterface $valueReader = new FlatValueReader(),
    ) {
        $this->coreFilterHandlers = $this->prepareFilterHandlers([
            new AllHandler(),
            new NoneHandler(),
            new AndXHandler(),
            new OrXHandler(),
            new BetweenHandler(),
            new EqualsHandler(),
            new EqualsNullHandler(),
            new GreaterThanHandler(),
            new GreaterThanOrEqualHandler(),
            new InHandler(),
            new LessThanHandler(),
            new LessThanOrEqualHandler(),
            new LikeHandler(),
            new NotHandler(),
        ]);
        $this->context = new Context($this->coreFilterHandlers, $this->valueReader);
        $this->filter = new All();
    }

    public function withAddedFilterHandlers(IterableFilterHandlerInterface ...$filterHandlers): self
    {
        $new = clone $this;
        $new->context = new Context(
            array_merge(
                $this->coreFilterHandlers,
                $this->prepareFilterHandlers($filterHandlers),
            ),
            $this->valueReader,
        );
        return $new;
    }

    public function withFilter(FilterInterface $filter): static
    {
        $new = clone $this;
        $new->filter = $filter;
        return $new;
    }

    /**
     * @psalm-return $this
     */
    public function withLimit(?int $limit): static
    {
        if ($limit < 0) {
            throw new InvalidArgumentException('The limit must not be less than 0.');
        }

        $new = clone $this;
        $new->limit = $limit;
        return $new;
    }

    /**
     * @psalm-return $this
     */
    public function withOffset(int $offset): static
    {
        $new = clone $this;
        $new->offset = $offset;
        return $new;
    }

    /**
     * @psalm-return $this
     */
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
        return count($this->internalRead(useLimitAndOffset: false));
    }

    /**
     * @psalm-return array<TKey, TValue>
     */
    public function read(): array
    {
        return $this->internalRead(useLimitAndOffset: true);
    }

    public function readOne(): array|object|null
    {
        if ($this->limit === 0) {
            return null;
        }

        /** @infection-ignore-all Any value more than one in `withLimit()` will be ignored because returned `current()` */
        return $this
            ->withLimit(1)
            ->getIterator()
            ->current();
    }

    public function getFilter(): FilterInterface
    {
        return $this->filter;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @psalm-return array<TKey, TValue>
     */
    private function internalRead(bool $useLimitAndOffset): array
    {
        $data = [];
        $skipped = 0;
        $sortedData = $this->sort === null ? $this->data : $this->sortItems($this->data, $this->sort);

        foreach ($sortedData as $key => $item) {
            // Don't return more than limit items.
            if ($useLimitAndOffset && $this->limit > 0 && count($data) === $this->limit) {
                /** @infection-ignore-all Here continue === break */
                break;
            }

            // Skip offset items.
            if ($useLimitAndOffset && $skipped < $this->offset) {
                ++$skipped;
                continue;
            }

            // Filter items
            if ($this->matchFilter($item, $this->filter)) {
                $data[$key] = $item;
            }
        }

        return $data;
    }

    /**
     * Return whether an item matches iterable filter.
     *
     * @param array|object $item Item to check.
     * @param FilterInterface $filter Filter.
     *
     * @return bool Whether an item matches iterable filter.
     */
    private function matchFilter(array|object $item, FilterInterface $filter): bool
    {
        $handler = $this->context->getFilterHandler($filter::class);
        return $handler->match($item, $filter, $this->context);
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
            /** @infection-ignore-all */
            uasort(
                $items,
                static function (array|object $itemA, array|object $itemB) use ($criteria) {
                    foreach ($criteria as $key => $order) {
                        $valueA = ArrayHelper::getValue($itemA, $key);
                        $valueB = ArrayHelper::getValue($itemB, $key);

                        if ($valueB === $valueA) {
                            continue;
                        }

                        return ($valueA > $valueB xor $order === SORT_DESC) ? 1 : -1;
                    }

                    return 0;
                },
            );
        }

        return $items;
    }

    /**
     * @param IterableFilterHandlerInterface[] $filterHandlers
     *
     * @return IterableFilterHandlerInterface[]
     * @psalm-return array<string, IterableFilterHandlerInterface>
     */
    private function prepareFilterHandlers(array $filterHandlers): array
    {
        $result = [];
        foreach ($filterHandlers as $filterHandler) {
            $result[$filterHandler->getFilterClass()] = $filterHandler;
        }
        return $result;
    }

    /**
     * Convert iterable to array.
     *
     * @param iterable $iterable Iterable to convert.
     *
     * @psalm-param iterable<TKey, TValue> $iterable
     *
     * @return array Resulting array.
     * @psalm-return array<TKey, TValue>
     */
    private function iterableToArray(iterable $iterable): array
    {
        return $iterable instanceof Traversable ? iterator_to_array($iterable) : $iterable;
    }
}
