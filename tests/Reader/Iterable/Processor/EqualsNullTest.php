<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\Processor;

use InvalidArgumentException;
use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Reader\Iterable\Processor\EqualsNull;
use Yiisoft\Data\Tests\TestCase;

final class EqualsNullTest extends TestCase
{
    public function matchDataProvider(): array
    {
        return [
            [true, ['value' => null]],
            [false, ['value' => false]],
            [false, ['value' => true]],
            [false, ['value' => 0]],
            [false, ['value' => 0.0]],
            [false, ['value' => 42]],
            [false, ['value' => '']],
            [false, ['value' => 'null']],
        ];
    }

    /**
     * @dataProvider matchDataProvider
     */
    public function testMatch(bool $expected, array $item): void
    {
        $this->assertSame($expected, (new EqualsNull())->match($item, ['value'], []));
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

        (new EqualsNull())->match(['id' => 1], $arguments, []);
    }

    /**
     * @dataProvider invalidStringValueDataProvider
     */
    public function testMatchFailForInvalidFieldValue($field): void
    {
        $type = FilterDataValidationHelper::getValueType($field);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The field should be string. The $type is received.");

        (new EqualsNull())->match(['id' => 1], [$field], []);
    }
}
