<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Support\CustomFilter;

use Yiisoft\Data\Reader\FilterInterface;

final class Digital implements FilterInterface
{
    public function __construct(public readonly string $field) {}
}
