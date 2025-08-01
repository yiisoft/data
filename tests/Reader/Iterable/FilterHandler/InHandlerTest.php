<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\FilterHandler;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Data\Reader\Iterable\Context;
use Yiisoft\Data\Reader\Iterable\FilterHandler\InHandler;
use Yiisoft\Data\Reader\Iterable\ValueReader\FlatValueReader;
use Yiisoft\Data\Tests\TestCase;

final class InHandlerTest extends TestCase
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

        $context = new Context([], new FlatValueReader());

        $this->assertSame($expected, $handler->match($item, new In('value', $value), $context));
    }
}
