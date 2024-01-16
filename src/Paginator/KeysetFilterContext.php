<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

final class KeysetFilterContext
{
    public function __construct(
        public string $field,
        public string $value,
        public int $sorting,
        public bool $isReverse
    ) {
    }
}
