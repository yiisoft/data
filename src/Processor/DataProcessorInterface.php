<?php

declare(strict_types=1);

namespace Yiisoft\Data\Processor;

/**
 * Data processor takes an iterator with source items and produces an iterator with processed items.
 *
 * @template TKey as array-key
 * @template TValue as mixed
 */
interface DataProcessorInterface
{
    /**
     * Apply some processing to source items and return results.
     *
     * @param iterable $items Source items iterator.
     * @psalm-param iterable<TKey, TValue> $items
     *
     * @return iterable Processed items iterator.
     * @psalm-return iterable<TKey, TValue>
     *
     * @throws DataProcessorException In case there is an error processing items.
     */
    public function process(iterable $items): iterable;
}
