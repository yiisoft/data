<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\Processor;

use InvalidArgumentException;
use stdClass;
use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Reader\Iterable\Handler\Any;
use Yiisoft\Data\Reader\Iterable\Handler\Equals;
use Yiisoft\Data\Reader\Iterable\Handler\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Iterable\Handler\IterableHandlerInterface;
use Yiisoft\Data\Reader\Iterable\Handler\LessThanOrEqual;
use Yiisoft\Data\Tests\TestCase;

final class AnyTest extends TestCase
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
                true,
                [[['=', 'value', 44], ['>=', 'value', 45], ['<=', 'value', 45]]],
                ['=' => new Equals(), '>=' => new GreaterThanOrEqual(), '<=' => new LessThanOrEqual()],
            ],
            [
                true,
                [[['=', 'value', 45], ['>=', 'value', 46], ['<=', 'value', 45]]],
                ['=' => new Equals(), '>=' => new GreaterThanOrEqual(), '<=' => new LessThanOrEqual()],
            ],
            [
                true,
                [[['=', 'value', 45], ['>=', 'value', 45], ['<=', 'value', 44]]],
                ['=' => new Equals(), '>=' => new GreaterThanOrEqual(), '<=' => new LessThanOrEqual()],
            ],
            [
                true,
                [[['=', 'value', 45], ['>=', 'value', 45], ['<=', 'value', 44]]],
                ['=' => new Equals(), '>=' => new GreaterThanOrEqual(), '<=' => new LessThanOrEqual()],
            ],
            [
                false,
                [[['=', 'value', 44], ['>=', 'value', 46], ['<=', 'value', 44]]],
                ['=' => new Equals(), '>=' => new GreaterThanOrEqual(), '<=' => new LessThanOrEqual()],
            ],
        ];
    }

    /**
     * @dataProvider matchDataProvider
     */
    public function testMatch(bool $expected, array $arguments, array $filterProcessors): void
    {
        $processor = new Any();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, $filterProcessors));
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

        (new Any())->match(['id' => 1], $arguments, []);
    }

    /**
     * @dataProvider invalidArrayValueDataProvider
     */
    public function testMatchFailIfSubFiltersIsNotArray($subFilters): void
    {
        $type = FilterDataValidationHelper::getValueType($subFilters);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The sub filters should be array. The $type is received.");

        (new Any())->match(['id' => 1], [$subFilters], []);
    }

    /**
     * @dataProvider invalidArrayValueDataProvider
     */
    public function testMatchFailIfSubFilterIsNotArray($subFilters): void
    {
        $type = FilterDataValidationHelper::getValueType($subFilters);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The sub filter should be array. The $type is received.");

        (new Any())->match(['id' => 1], [[$subFilters]], []);
    }

    public function testMatchFailIfArgumentValueIsEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least operator should be provided.');

        (new Any())->match(['id' => 1], [[[]]], []);
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
        $type = FilterDataValidationHelper::getValueType($filter[0]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The operator should be string. The $type is received.");

        (new Any())->match(['id' => 1], [[$filter]], []);
    }

    public function testMatchFailForEmptyFilterOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The operator string cannot be empty.');

        (new Any())->match(['id' => 1], [[['']]], []);
    }

    public function testMatchFailIfFilterOperatorIsNotSupported(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('">" operator is not supported.');

        (new Any())->match(['id' => 1], [[['>']]], ['=' => new Equals()]);
    }

    public function testMatchFailIfFilterProcessorIsNotIterable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The filter processor should be an object and implement "%s". The %s is received.',
            IterableHandlerInterface::class,
            stdClass::class,
        ));

        (new Any())->match(['id' => 1], [[['=']]], ['=' => new stdClass()]);
    }
}
