<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Support;

final class Car
{
    public function __construct(
        private ?int $number,
    ) {}

    public function getNumber(): ?int
    {
        return $this->number;
    }
}
