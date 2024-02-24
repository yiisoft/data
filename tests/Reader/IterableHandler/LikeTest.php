<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\EqualsEmpty;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LikeHandler;
use Yiisoft\Data\Tests\TestCase;

final class LikeTest extends TestCase
{
    public static function matchDataProvider(): array
    {
        return [
            [true, 'value', 'Great Cat Fighter'],
            [true, 'value', 'Cat'],
            [false, 'id', '1'],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, string $field, string $value): void
    {
        $processor = new LikeHandler();

        $item = [
            'id' => 1,
            'value' => 'Great Cat Fighter',
        ];

        $this->assertSame($expected, $processor->match($item, new Like($field, $value), []));
    }

    public function testInvalidFilter(): void
    {
        $handler = new LikeHandler();
        $filter = new EqualsEmpty('test');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect filter.');
        $handler->match([], $filter, []);
    }
}
