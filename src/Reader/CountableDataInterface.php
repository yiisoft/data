<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader;

use Countable;

interface CountableDataInterface extends Countable
{
    public function count(): int;
}
