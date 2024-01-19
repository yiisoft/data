<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Yiisoft\Data\Reader\Iterable\FilterHandler\AnyHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\GreaterThanOrEqualHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\LessThanOrEqualHandler;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;
use Yiisoft\Data\Tests\TestCase;

final class AnyTest extends TestCase
{
    public static function matchDataProvider(): array
    {
        return [
            [
                true,
                [[['=', 'value', 45], ['>=', 'value', 45], ['<=', 'value', 45]]],
                ['=' => new EqualsHandler(), '>=' => new GreaterThanOrEqualHandler(), '<=' => new LessThanOrEqualHandler()],
            ],
            [
                true,
                [[['=', 'value', '45'], ['>=', 'value', 45], ['<=', 'value', 45]]],
                ['=' => new EqualsHandler(), '>=' => new GreaterThanOrEqualHandler(), '<=' => new LessThanOrEqualHandler()],
            ],
            [
                true,
                [[['=', 'value', 44], ['>=', 'value', 45], ['<=', 'value', 45]]],
                ['=' => new EqualsHandler(), '>=' => new GreaterThanOrEqualHandler(), '<=' => new LessThanOrEqualHandler()],
            ],
            [
                true,
                [[['=', 'value', 45], ['>=', 'value', 46], ['<=', 'value', 45]]],
                ['=' => new EqualsHandler(), '>=' => new GreaterThanOrEqualHandler(), '<=' => new LessThanOrEqualHandler()],
            ],
            [
                true,
                [[['=', 'value', 45], ['>=', 'value', 45], ['<=', 'value', 44]]],
                ['=' => new EqualsHandler(), '>=' => new GreaterThanOrEqualHandler(), '<=' => new LessThanOrEqualHandler()],
            ],
            [
                true,
                [[['=', 'value', 45], ['>=', 'value', 45], ['<=', 'value', 44]]],
                ['=' => new EqualsHandler(), '>=' => new GreaterThanOrEqualHandler(), '<=' => new LessThanOrEqualHandler()],
            ],
            [
                false,
                [[['=', 'value', 44], ['>=', 'value', 46], ['<=', 'value', 44]]],
                ['=' => new EqualsHandler(), '>=' => new GreaterThanOrEqualHandler(), '<=' => new LessThanOrEqualHandler()],
            ],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, array $arguments, array $filterHandlers): void
    {
        $handler = new AnyHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $handler->match($item, $arguments, $filterHandlers));
    }

    public static function invalidCountArgumentsDataProvider(): array
    {
        return [
            'zero' => [[]],
            'two' => [[1, 2]],
            'tree' => [[1, 2]],
            'four' => [[1, 2, 3, 4]],
        ];
    }

    #[DataProvider('invalidCountArgumentsDataProvider')]
    public function testMatchFailForInvalidCountArguments($arguments): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('$arguments should contain exactly one element.');

        (new AnyHandler())->match(['id' => 1], $arguments, []);
    }

    #[DataProvider('invalidArrayValueDataProvider')]
    public function testMatchFailIfSubFiltersIsNotArray($subFilters): void
    {
        $type = get_debug_type($subFilters);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The sub filters should be array. The $type is received.");

        (new AnyHandler())->match(['id' => 1], [$subFilters], []);
    }

    #[DataProvider('invalidArrayValueDataProvider')]
    public function testMatchFailIfSubFilterIsNotArray($subFilters): void
    {
        $type = get_debug_type($subFilters);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The sub filter should be array. The $type is received.");

        (new AnyHandler())->match(['id' => 1], [[$subFilters]], []);
    }

    public function testMatchFailIfArgumentValueIsEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least operator should be provided.');

        (new AnyHandler())->match(['id' => 1], [[[]]], []);
    }

    public static function invalidFilterOperatorDataProvider(): array
    {
        $data = parent::invalidFilterOperatorDataProvider();
        unset($data['array'], $data['empty-string']);
        return $data;
    }

    #[DataProvider('invalidFilterOperatorDataProvider')]
    public function testMatchFailForInvalidFilterOperator(array $filter): void
    {
        $type = get_debug_type($filter[0]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The operator should be string. The $type is received.");

        (new AnyHandler())->match(['id' => 1], [[$filter]], []);
    }

    public function testMatchFailForEmptyFilterOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The operator string cannot be empty.');

        (new AnyHandler())->match(['id' => 1], [[['']]], []);
    }

    public function testMatchFailIfFilterOperatorIsNotSupported(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('">" operator is not supported.');

        (new AnyHandler())->match(['id' => 1], [[['>']]], ['=' => new EqualsHandler()]);
    }

    public function testMatchFailIfFilterHandlerIsNotIterable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The filter handler should be an object and implement "%s". The %s is received.',
            IterableFilterHandlerInterface::class,
            stdClass::class,
        ));

        (new AnyHandler())->match(['id' => 1], [[['=']]], ['=' => new stdClass()]);
    }
}
