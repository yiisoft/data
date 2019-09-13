<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor;


class FilterProcessor
{
    /**
     * @var Processor[]
     */
    private $processors = [];

    public function withProcessors(Processor... $processors): self
    {
        $new = clone $this;
        $new->putProcessors($processors);
        return $new;
    }

    public function putProcessors(Processor... $processors)
    {
        foreach ($processors as $processor) {
            $operator = $processor->getOperator();
            $this->processors[$operator] = [];
            $this->processors[$operator][$processor->getGroup()] = $processor->withFilterProcessor($this);
        }
    }

    public function getProcessor($group, $operator): Processor
    {
        if(!isset($this->processors[$operator][$group])) {
            throw new \RuntimeException(sprintf('Operation "%s.%s" is not supported', $group, $operator));
        }
        return $this->processors[$operator][$group];
    }


}