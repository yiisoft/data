<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsEmptyHandler;
use Yiisoft\Data\Tests\TestCase;

final class EqualsEmptyTest extends TestCase
{
    public function matchDataProvider(): array
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

    /**
     * @dataProvider matchDataProvider
     */
    public function testMatch(bool $expected, array $item): void
    {
        $this->assertSame($expected, (new EqualsEmptyHandler())->match($item, ['value'], []));
    }

    public function invalidCountArgumentsDataProvider(): array
    {
        return [
            'zero' => [[]],
            'two' => [[1, 2]],
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
        $this->expectExceptionMessage('$arguments should contain exactly one element.');

        (new EqualsEmptyHandler())->match(['id' => 1], $arguments, []);
    }

    /**
     * @dataProvider invalidStringValueDataProvider
     */
    public function testMatchFailForInvalidFieldValue($field): void
    {
        $type = get_debug_type($field);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The field should be string. The $type is received.");

        (new EqualsEmptyHandler())->match(['id' => 1], [$field], []);
    }
}
