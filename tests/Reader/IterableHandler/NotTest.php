<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use InvalidArgumentException;
use stdClass;
use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Reader\IterableFilterHandler\Equals;
use Yiisoft\Data\Reader\IterableFilterHandler\Not;
use Yiisoft\Data\Reader\IterableFilterHandlerInterface;
use Yiisoft\Data\Tests\TestCase;

final class NotTest extends TestCase
{
    public function matchDataProvider(): array
    {
        return [
            [true, [['=', 'value', 44]], ['=' => new Equals()]],
            [true, [['=', 'value', 46]], ['=' => new Equals()]],
            [false, [['=', 'value', 45]], ['=' => new Equals()]],
            [false, [['=', 'value', '45']], ['=' => new Equals()]],
        ];
    }

    /**
     * @dataProvider matchDataProvider
     */
    public function testMatch(bool $expected, array $arguments, array $filterHandlers): void
    {
        $processor = new Not();

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

        (new Not())->match(['id' => 1], $arguments, []);
    }

    /**
     * @dataProvider invalidArrayValueDataProvider
     */
    public function testMatchFailIfArgumentValueIsNotArray($value): void
    {
        $type = FilterDataValidationHelper::getValueType($value);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The values should be array. The $type is received.");

        (new Not())->match(['id' => 1], [$value], []);
    }

    public function testMatchFailIfArgumentValueIsEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least operator should be provided.');

        (new Not())->match(['id' => 1], [[]], []);
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

        (new Not())->match(['id' => 1], [$filter], []);
    }

    public function testMatchFailForEmptyFilterOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The operator string cannot be empty.');

        (new Not())->match(['id' => 1], [['']], []);
    }

    public function testMatchFailIfFilterOperatorIsNotSupported(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('">" operator is not supported.');

        (new Not())->match(['id' => 1], [['>']], ['=' => new Equals()]);
    }

    public function testMatchFailIfFilterHandlerIsNotIterable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The filter handler should be an object and implement "%s". The %s is received.',
            IterableFilterHandlerInterface::class,
            stdClass::class,
        ));

        (new Not())->match(['id' => 1], [['=']], ['=' => new stdClass()]);
    }
}
