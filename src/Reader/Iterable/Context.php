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
         * @psalm-var array<string, IterableFilterHandlerInterface>
         */
        public readonly array $iterableFilterHandlers,
        private readonly ValueReaderInterface $valueReader,
    ) {
    }

    /**
     * @psalm-param class-string<FilterInterface> $class
     */
    public function getFilterHandler(string $class): IterableFilterHandlerInterface
    {
        return $this->iterableFilterHandlers[$class]
            ?? throw new LogicException(
                sprintf('Filter "%s" is not supported.', $class),
            );
    }

    public function readValue(array|object $item, string $field): mixed
    {
        return $this->valueReader->read($item, $field);
    }
}
