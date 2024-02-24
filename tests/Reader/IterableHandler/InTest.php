<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Data\Reader\Iterable\FilterHandler\InHandler;
use Yiisoft\Data\Tests\TestCase;

final class InTest extends TestCase
{
    public static function matchDataProvider(): array
    {
        return [
            [true,  [44, 45, 46]],
            [true,  [44, '45', 46]],
            [false, [1, 2, 3]],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, array $value): void
    {
        $handler = new InHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $handler->match($item, new In('value', $value), []));
    }
}
