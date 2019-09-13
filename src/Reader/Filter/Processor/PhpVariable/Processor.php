<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor\PhpVariable;


use Yiisoft\Data\Reader\Filter\Processor\FilterProcessor;
use Yiisoft\Data\Reader\Filter\Processor\PhpVariableFilterProcessor;

abstract class Processor implements \Yiisoft\Data\Reader\Filter\Processor\Processor
{
    /**
     * @var FilterProcessor
     */
    private $filterProcessor;

    /**
     * PHP variable specific execute
     */
    abstract function execute(array $item, array $arguments): bool;

    public function getGroup(): string
    {
        return PhpVariableFilterProcessor::PROCESSOR_GROUP;
    }

    public function getFilterProcessor(): FilterProcessor
    {
        return $this->filterProcessor;
    }

    public function withFilterProcessor(FilterProcessor $filterProcessor): self
    {
        $new = clone $this;
        $new->filterProcessor = $filterProcessor;
        return $new;
    }

}