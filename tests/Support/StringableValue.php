<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Support;

use Stringable;

final class StringableValue implements Stringable
{
    public function __construct(
        private readonly string $value,
    ) {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
