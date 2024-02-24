<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\EqualsEmpty;
use Yiisoft\Data\Reader\Filter\EqualsNull;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsEmptyHandler;
use Yiisoft\Data\Tests\TestCase;

final class EqualsEmptyTest extends TestCase
{
    public static function matchDataProvider(): array
    {
        return [
            [true, ['value' => null]],
            [true, ['value' => false]],
            [true, ['value' => 0]],
            [true, ['value' => 0.0]],
            [true, ['value' => '0']],
            [true, ['value' => '']],
            [false, ['value' => 42]],
            [false, ['value' => '1']],
            [false, ['value' => true]],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, array $item): void
    {
        $this->assertSame($expected, (new EqualsEmptyHandler())->match($item, new EqualsEmpty('value'), []));
    }
}
