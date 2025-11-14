<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

final class PageToken
{
    private function __construct(
        public readonly string $value,
        public readonly bool $isPrevious,
    ) {}

    public static function previous(string $value): self
    {
        return new self($value, true);
    }

    public static function next(string $value): self
    {
        return new self($value, false);
    }
}
