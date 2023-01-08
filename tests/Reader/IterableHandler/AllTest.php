<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use InvalidArgumentException;
use stdClass;
use Yiisoft\Data\Reader\IterableFilterHandler\All;
use Yiisoft\Data\Reader\IterableFilterHandler\Equals;
use Yiisoft\Data\Reader\IterableFilterHandler\GreaterThanOrEqual;
use Yiisoft\Data\Reader\IterableFilterHandler\LessThanOrEqual;
use Yiisoft\Data\Reader\IterableFilterHandlerInterface;
use Yiisoft\Data\Tests\TestCase;

final class AllTest extends TestCase
{
    public function matchDataProvider(): array
    {
        return [
            [
                true,
                [[['=', 'value', 45], ['>=', 'value', 45], ['<=', 'value', 45]]],
                ['=' => new Equals(), '>=' => new GreaterThanOrEqual(), '<=' => new LessThanOrEqual()],
            ],
            [
                true,
                [[['=', 'value', '45'], ['>=', 'value', 45], ['<=', 'value', 45]]],
                ['=' => new Equals(), '>=' => new GreaterThanOrEqual(), '<=' => new LessThanOrEqual()],
            ],
            [
                false,
                [[['=', 'value', 44], ['>=', 'value', 45], ['<=', 'value', 45]]],
                ['=' => new Equals(), '>=' => new GreaterThanOrEqual(), '<=' => new LessThanOrEqual()],
            ],
            [
                false,
                [[['=', 'value', 45], ['>=', 'value', 46], ['<=', 'value', 45]]],
                ['=' => new Equals(), '>=' => new GreaterThanOrEqual(), '<=' => new LessThanOrEqual()],
            ],
            [
                false,
                [[['=', 'value', 45], ['>=', 'value', 45], ['<=', 'value', 44]]],
                ['=' => new Equals(), '>=' => new GreaterThanOrEqual(), '<=' => new LessThanOrEqual()],
            ],
            [
                false,
                [[['=', 'value', 45], ['>=', 'value', 45], ['<=', 'value', 44]]],
                ['=' => new Equals(), '>=' => new GreaterThanOrEqual(), '<=' => new LessThanOrEqual()],
            ],
        ];
    }

    /**
     * @dataProvider matchDataProvider
     */
    public function testMatch(bool $expected, array $arguments, array $filterHandlers): void
    {
        $processor = new All();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, $filterHandlers));
    }

    public function invalidCountArgumentsDataProvider(): array
    {
        return [
            'zero' => [[]],
            'two' => [[1, 2]],
            'tree' => [[1, 2]],
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

        (new All())->match(['id' => 1], $arguments, []);
    }

    /**
     * @dataProvider invalidArrayValueDataProvider
     */
    public function testMatchFailIfSubFiltersIsNotArray($subFilters): void
    {
        $type = get_debug_type($subFilters);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The sub filters should be array. The $type is received.");

        (new All())->match(['id' => 1], [$subFilters], []);
    }

    /**
     * @dataProvider invalidArrayValueDataProvider
     */
    public function testMatchFailIfSubFilterIsNotArray($subFilters): void
    {
        $type = get_debug_type($subFilters);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The sub filter should be array. The $type is received.");

        (new All())->match(['id' => 1], [[$subFilters]], []);
    }

    public function testMatchFailIfArgumentValueIsEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least operator should be provided.');

        (new All())->match(['id' => 1], [[[]]], []);
    }

    public function invalidFilterOperatorDataProvider(): array
    {
        $data = parent::invalidFilterOperatorDataProvider();
        unset($data['array'], $data['empty-string']);
        return $data;
    }

    /**
     * @dataProvider invalidFilterOperatorDataProvider
     */
    public function testMatchFailForInvalidFilterOperator(array $filter): void
    {
        $type = get_debug_type($filter[0]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The operator should be string. The $type is received.");

        (new All())->match(['id' => 1], [[$filter]], []);
    }

    public function testMatchFailForEmptyFilterOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The operator string cannot be empty.');

        (new All())->match(['id' => 1], [[['']]], []);
    }

    public function testMatchFailIfFilterOperatorIsNotSupported(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('">" operator is not supported.');

        (new All())->match(['id' => 1], [[['>']]], ['=' => new Equals()]);
    }

    public function testMatchFailIfFilterHandlerIsNotIterable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The filter handler should be an object and implement "%s". The %s is received.',
            IterableFilterHandlerInterface::class,
            stdClass::class,
        ));

        (new All())->match(['id' => 1], [[['=']]], ['=' => new stdClass()]);
    }
}
