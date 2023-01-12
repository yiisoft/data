<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LikeHandler;
use Yiisoft\Data\Tests\TestCase;

final class LikeTest extends TestCase
{
    public function matchDataProvider(): array
    {
        return [
            [true, ['value', 'Great Cat Fighter']],
            [true, ['value', 'Cat']],
            [false, ['id', 1]],
            [false, ['id', '1']],
        ];
    }

    /**
     * @dataProvider matchDataProvider
     */
    public function testMatch(bool $expected, array $arguments): void
    {
        $processor = new LikeHandler();

        $item = [
            'id' => 1,
            'value' => 'Great Cat Fighter',
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }

    public function invalidCountArgumentsDataProvider(): array
    {
        return [
            'zero' => [[]],
            'one' => [[1]],
            'three' => [[1, 2, 3]],
            'four' => [[1, 2, 3, 4]],
        ];
    }

    /**
     * @dataProvider invalidCountArgumentsDataProvider
     */
    public function testMatchFailForInvalidCountArguments($arguments): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$arguments should contain exactly two elements.');

        (new LikeHandler())->match(['id' => 1], $arguments, []);
    }

    /**
     * @dataProvider invalidStringValueDataProvider
     */
    public function testMatchFailForInvalidFieldValue($field): void
    {
        $type = get_debug_type($field);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The field should be string. The $type is received.");

        (new LikeHandler())->match(['id' => 1], [$field, 1], []);
    }
}
