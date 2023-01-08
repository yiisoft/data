<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\Processor;

use DateTimeImmutable;
use InvalidArgumentException;
use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Reader\Iterable\Handler\Between;
use Yiisoft\Data\Tests\TestCase;

final class BetweenTest extends TestCase
{
    public function matchScalarDataProvider(): array
    {
        return [
            [true, ['value', 42, 47]],
            [true, ['value', 45, 45]],
            [true, ['value', 45, 46]],
            [false, ['value', 46, 47]],
            [false, ['value', 46, 45]],
        ];
    }

    /**
     * @dataProvider matchScalarDataProvider
     */
    public function testMatchScalar(bool $expected, array $arguments): void
    {
        $processor = new Between();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }

    public function matchDateTimeInterfaceDataProvider(): array
    {
        return [
            [true, ['value', new DateTimeImmutable('2022-02-22 16:00:42'), new DateTimeImmutable('2022-02-22 16:00:47')]],
            [true, ['value', new DateTimeImmutable('2022-02-22 16:00:45'), new DateTimeImmutable('2022-02-22 16:00:45')]],
            [true, ['value', new DateTimeImmutable('2022-02-22 16:00:45'), new DateTimeImmutable('2022-02-22 16:00:46')]],
            [false, ['value', new DateTimeImmutable('2022-02-22 16:00:46'), new DateTimeImmutable('2022-02-22 16:00:47')]],
            [false, ['value', new DateTimeImmutable('2022-02-22 16:00:46'), new DateTimeImmutable('2022-02-22 16:00:45')]],
        ];
    }

    /**
     * @dataProvider matchDateTimeInterfaceDataProvider
     */
    public function testMatchDateTimeInterface(bool $expected, array $arguments): void
    {
        $processor = new Between();

        $item = [
            'id' => 1,
            'value' => new DateTimeImmutable('2022-02-22 16:00:45'),
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }

    public function invalidCountArgumentsDataProvider(): array
    {
        return [
            'zero' => [[]],
            'one' => [[1]],
            'two' => [[1, 2]],
            'four' => [[1, 2, 3, 4]],
        ];
    }

    /**
     * @dataProvider invalidCountArgumentsDataProvider
     */
    public function testMatchFailForInvalidCountArguments($arguments): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$arguments should contain exactly three elements.');

        (new Between())->match(['id' => 1], $arguments, []);
    }

    /**
     * @dataProvider invalidStringValueDataProvider
     */
    public function testMatchFailForInvalidFieldValue($field): void
    {
        $type = FilterDataValidationHelper::getValueType($field);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The field should be string. The $type is received.");

        (new Between())->match(['id' => 1], [$field, 1, 2], []);
    }
}
