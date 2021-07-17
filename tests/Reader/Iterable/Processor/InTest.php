<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\Processor;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Iterable\Processor\In;

final class InTest extends TestCase
{
    public function dataBase(): array
    {
        return [
            [true, ['weight', [44, 45, 46]]],
            [false, ['weight', [1, 2, 3]]],
            [false, ['age', [45]]],
        ];
    }

    /**
     * @dataProvider dataBase
     */
    public function testBase(bool $expected, array $arguments): void
    {
        $processor = new In();

        $item = [
            'id' => 1,
            'weight' => 45,
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }
}
