<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Iterable\FilterHandler\GreaterThanHandler;
use Yiisoft\Data\Tests\TestCase;

final class GreaterThanTest extends TestCase
{
    public static function matchScalarDataProvider(): array
    {
        return [
            [true, ['value', 44]],
            [true, ['value', '44']],
            [false, ['value', 45]],
            [false, ['value', 46]],
        ];
    }

    #[DataProvider('matchScalarDataProvider')]
    public function testMatchScalar(bool $expected, array $arguments): void
    {
        $processor = new GreaterThanHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }

    public static function matchDateTimeInterfaceDataProvider(): array
    {
        return [
            [true, ['value', new DateTimeImmutable('2022-02-22 16:00:44')]],
            [false, ['value', new DateTimeImmutable('2022-02-22 16:00:45')]],
            [false, ['value', new DateTimeImmutable('2022-02-22 16:00:46')]],
        ];
    }

    #[DataProvider('matchDateTimeInterfaceDataProvider')]
    public function testMatchDateTimeInterface(bool $expected, array $arguments): void
    {
        $processor = new GreaterThanHandler();

        $item = [
            'id' => 1,
            'value' => new DateTimeImmutable('2022-02-22 16:00:45'),
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, []));
    }

    public static function invalidCountArgumentsDataProvider(): array
    {
        return [
            'zero' => [[]],
            'one' => [[1]],
            'three' => [[1, 2, 3]],
            'four' => [[1, 2, 3, 4]],
        ];
    }

    #[DataProvider('invalidCountArgumentsDataProvider')]
    public function testMatchFailForInvalidCountArguments($arguments): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$arguments should contain exactly two elements.');

        (new GreaterThanHandler())->match(['id' => 1], $arguments, []);
    }

    #[DataProvider('invalidStringValueDataProvider')]
    public function testMatchFailForInvalidFieldValue($field): void
    {
        $type = get_debug_type($field);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The field should be string. The $type is received.");

        (new GreaterThanHandler())->match(['id' => 1], [$field, 1], []);
    }
}
