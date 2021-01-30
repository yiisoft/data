<?php

declare(strict_types=1);

namespace Yiisoft\Data\Writer;

interface DeletableInterface
{
    public function delete(iterable $items): void;
}
