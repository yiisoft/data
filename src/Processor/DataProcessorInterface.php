<?php
namespace Yiisoft\Data\Processor;

interface DataProcessorInterface
{
    public function process(array $items): array;
}
