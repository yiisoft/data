<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Iterable\Processor;

use Yiisoft\Data\Reader\Filter\FilterProcessorInterface;

use function array_key_exists;
use function is_string;

class Like implements IterableProcessorInterface, FilterProcessorInterface
{
    public function getOperator(): string
    {
        return \Yiisoft\Data\Reader\Filter\Like::getOperator();
    }

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        if (count($arguments) !== 2) {
            throw new \InvalidArgumentException('$arguments should contain exactly two elements');
        }
        [$field, $value] = $arguments;
        return array_key_exists($field, $item) &&
            is_string($item[$field]) &&
            stripos($item[$field], (string)$value) !== false;
    }
}
