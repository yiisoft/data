<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\Processor;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Iterable\Processor\LessThanOrEqual;

final class LessThanOrEqualTest extends TestCase
{
    public function dataBase(): array
    {
        return [
            [true, ['weight', 46]],
            [true, ['weight', 45]],
            [false, ['weight', 44]],
            [false, ['age', 25]],
        ];
    }

    /**
     * @dataProvider dataBase
     */
    public function testBase(bool $expected, array $arguments): void
    {
        $processor = new LessThanOrEqual();

        $item = [
            'id' => 1,
            'weight' => 45,
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }
}
