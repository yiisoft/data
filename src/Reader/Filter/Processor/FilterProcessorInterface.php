<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor;


interface FilterProcessorInterface
{
    public function getOperator(): string;
}
