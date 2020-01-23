<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

interface CountableDataInterface extends \Countable
{
    public function count(): int;
}
