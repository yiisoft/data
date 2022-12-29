<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable;

use InvalidArgumentException;
use RuntimeException;
use Traversable;
use Yiisoft\Arrays\ArrayHelper;
use Yiisoft\Data\Reader\Filter\FilterInterface;
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

use function array_key_exists;
use function is_string;

/**
 * @internal
 */
final class IterableHelper
{
    /**
     * @return IterableProcessorInterface[]
     */
    public static function getBuiltInFilterProcessors(): array
    {
        return [
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
            new Not(),
        ];
    }

    /**
     * @param IterableProcessorInterface[] $processors
     *
     * @return array<string, IterableProcessorInterface>
     */
    public static function prepareFilterProcessors(array $processors): array
    {
        $result = [];

        foreach ($processors as $processor) {
            self::assertIterableProcessorInterface($processor);
            $result[$processor->getOperator()] = $processor;
        }

        return $result;
    }

    /**
     * @throws InvalidArgumentException
     *
     * @psalm-assert-if-true IterableProcessorInterface $value
     */
    public static function assertIterableProcessorInterface(mixed $value): void
    {
        if (!$value instanceof IterableProcessorInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'The filter processor should be an object and implement "%s". Got "%s".',
                    IterableProcessorInterface::class,
                    get_debug_type($value),
                )
            );
        }
    }

    /**
     * @param array<string, IterableProcessorInterface> $filterProcessors
     */
    public static function matchFilter(array|object $item, array $filter, array $filterProcessors): bool
    {
        $operation = array_shift($filter);
        $arguments = $filter;

        if (!is_string($operation)) {
            throw new RuntimeException(
                sprintf(
                    'The operator should be string. The "%s" is received.',
                    get_debug_type($operation),
                )
            );
        }

        if ($operation === '') {
            throw new RuntimeException('The operator string cannot be empty.');
        }

        if (!array_key_exists($operation, $filterProcessors)) {
            throw new RuntimeException(sprintf('Operation "%s" is not supported.', $operation));
        }

        return $filterProcessors[$operation]->match($item, $arguments, $filterProcessors);
    }

    /**
     * @template TKey as array-key
     * @template TValue as array|object
     *
     * @psalm-param iterable<TKey, TValue> $items
     * @psalm-return iterable<TKey, TValue>
     */
    public static function sortItems(iterable $items, ?Sort $sort): iterable
    {
        $criteria = $sort?->getCriteria();

        if (empty($criteria)) {
            return $items;
        }

        $items = $items instanceof Traversable ? iterator_to_array($items) : $items;

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

        return $items;
    }
}
