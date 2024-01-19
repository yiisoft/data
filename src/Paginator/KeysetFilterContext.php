<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

final class KeysetFilterContext
{
    public function __construct(
        public readonly string $field,
        public readonly string $value,
        public readonly int $sorting,
        public readonly bool $isReverse
    ) {
    }
}
