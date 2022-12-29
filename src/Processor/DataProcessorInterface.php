<?php

declare(strict_types=1);

namespace Yiisoft\Data\Processor;

/**
 * @template TKey as array-key
 * @template TValue as array|object
 */
interface DataProcessorInterface
{
    /**
     * @param iterable<TKey, TValue> $items
     *
     * @return iterable<TKey, TValue>
     */
    public function process(iterable $items): iterable;
}
