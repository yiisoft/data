<?php

declare(strict_types=1);

namespace Yiisoft\Data\Processor;

/**
 * Data processor takes an iterator with source items and produces an iterator with processed items.
 *
 * @template TKey of array-key = array-key
 * @template TValue of mixed = mixed
 */
interface DataProcessorInterface
{
    /**
     * Apply some processing to source items and return results.
     *
     * @param iterable<TKey, TValue> $items Source items iterator.
     *
     * @throws DataProcessorException If there is an error while processing items.
     *
     * @return iterable<TKey, TValue> Processed items iterator.
     */
    public function process(iterable $items): iterable;
}
