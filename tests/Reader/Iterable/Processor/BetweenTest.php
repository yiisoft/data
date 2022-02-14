<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\Processor;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Iterable\Processor\Between;

final class BetweenTest extends TestCase
{
    public function dataBase(): array
    {
        return [
            [true, ['weight', 42, 47]],
            [true, ['weight', 45, 45]],
            [true, ['weight', 45, 46]],
            [false, ['weight', 46, 47]],
            [false, ['weight', 46, 45]],
            [false, ['age', 42, 47]],
        ];
    }

    /**
     * @dataProvider dataBase
     */
    public function testBase(bool $expected, array $arguments): void
    {
        $processor = new Between();

        $item = [
            'id' => 1,
            'weight' => 45,
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }
}
