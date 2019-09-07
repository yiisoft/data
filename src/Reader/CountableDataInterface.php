<?php
namespace Yiisoft\Data\Reader;

interface CountableDataInterface extends \Countable
{
    public function count(): int;
}
