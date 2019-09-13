<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor;


interface Processor
{
    public function getGroup(): string;
    public function withFilterProcessor(FilterProcessor $filterProcessor);
    public function getFilterProcessor(): FilterProcessor;
    public function getOperator(): string;
}