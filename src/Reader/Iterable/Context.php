<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable;

use LogicException;
use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\ValueReader\ValueReaderInterface;

use function sprintf;

final class Context
{
    public function __construct(
        /**
         * @var array<string, IterableFilterHandlerInterface<covariant FilterInterface>>
         */
        public readonly array $iterableFilterHandlers,
        private readonly ValueReaderInterface $valueReader,
    ) {}

    /**
     * @param class-string<FilterInterface> $class
     *
     * @return IterableFilterHandlerInterface<FilterInterface>
     */
    public function getFilterHandler(string $class): IterableFilterHandlerInterface
    {
        /**
         * @phpstan-ignore return.type (Each handler is registered only for the filter its `getFilterClass()` returns,
         * so a handler parameterized with its concrete filter type is safe to use where
         * `IterableFilterHandlerInterface<FilterInterface>` is expected.)
         */
        return $this->iterableFilterHandlers[$class]
            ?? throw new LogicException(
                sprintf('Filter "%s" is not supported.', $class),
            );
    }

    /**
     * Read a value from an item by the field name.
     *
     * @param array<mixed>|object $item Item to read value from.
     */
    public function readValue(array|object $item, string $field): mixed
    {
        return $this->valueReader->read($item, $field);
    }
}
