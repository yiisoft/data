<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter\Processor;


use Yiisoft\Data\Reader\Filter\Processor\PhpVariable\All;
use Yiisoft\Data\Reader\Filter\Processor\PhpVariable\Any;
use Yiisoft\Data\Reader\Filter\Processor\PhpVariable\Equals;
use Yiisoft\Data\Reader\Filter\Processor\PhpVariable\GreaterThan;
use Yiisoft\Data\Reader\Filter\Processor\PhpVariable\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Filter\Processor\PhpVariable\In;
use Yiisoft\Data\Reader\Filter\Processor\PhpVariable\LessThan;
use Yiisoft\Data\Reader\Filter\Processor\PhpVariable\LessThanOrEqual;
use Yiisoft\Data\Reader\Filter\Processor\PhpVariable\Like;
use Yiisoft\Data\Reader\Filter\Processor\PhpVariable\Not;

class PhpVariableFilterProcessor extends FilterProcessor
{
    public const PROCESSOR_GROUP = 'PhpVariable';

    public function __construct()
    {
        $this->putProcessors(
            new All(),
            new Any(),
            new Equals(),
            new GreaterThan(),
            new GreaterThanOrEqual(),
            new In(),
            new LessThan(),
            new LessThanOrEqual(),
            new Like(),
            new Not()
        );
    }

    public function match(array $item, array $filter) {
        $operation = array_shift($filter);
        $arguments = $filter;

        $processor = $this->getProcessor(self::PROCESSOR_GROUP, $operation);
        /* @var $processor \Yiisoft\Data\Reader\Filter\Processor\PhpVariable\Processor */
        return $processor->match($item, $arguments);
    }
}