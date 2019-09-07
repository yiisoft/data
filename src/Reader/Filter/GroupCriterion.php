<?php
declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

abstract class GroupCriterion implements FilterInterface
{
    /**
     * @var FilterInterface[]
     */
    private $criteria;

    public function __construct(FilterInterface...$criteria)
    {
        $this->criteria = $criteria;
    }

    public function toArray(): array
    {
        $criteriaArray = [];
        foreach ($this->criteria as $criterion) {
            $criteriaArray[] = $criterion->toArray();
        }
        return [static::getOperator(), $criteriaArray];
    }
}
