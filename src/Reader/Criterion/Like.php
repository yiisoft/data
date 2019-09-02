<?php


namespace Reader\Criterion;


use Yiisoft\Data\Reader\Criterion\CriteronInterface;

final class Like implements CriteronInterface
{
    private $field;
    private $value;

    public function __construct(string $field, string $value)
    {
        $this->field = $field;
        $this->value = $value;
    }


    public function toArray(): array
    {
        return ['like', $this->field, $this->value];
    }
}
