<?php

declare(strict_types=1);

namespace Yiisoft\Data\Processor;

interface DataProcessorInterface
{
    public function process(array $items): array;
}
