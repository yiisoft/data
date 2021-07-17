<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\Processor;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Iterable\Processor\GreaterThan;

final class GreaterThanTest extends TestCase
{
    public function dataBase(): array
    {
        return [
            [true, ['weight', 44]],
            [false, ['weight', 45]],
            [false, ['weight', 46]],
            [false, ['age', 25]],
        ];
    }

    /**
     * @dataProvider dataBase
     */
    public function testBase(bool $expected, array $arguments): void
    {
        $processor = new GreaterThan();

        $item = [
            'id' => 1,
            'weight' => 45,
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }
}
