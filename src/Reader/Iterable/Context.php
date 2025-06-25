<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable;

use Yiisoft\Data\Reader\FilterInterface;
use Yiisoft\Data\Reader\Iterable\ValueReader\ValueReaderInterface;

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
    public function tryFindFilterHandler(string $class): ?IterableFilterHandlerInterface
    {
        return $this->iterableFilterHandlers[$class] ?? null;
    }

    public function readValue(array|object $item, string $field): mixed
    {
        return $this->valueReader->read($item, $field);
    }
}
