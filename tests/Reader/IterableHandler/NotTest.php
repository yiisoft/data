<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\IterableHandler;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Yiisoft\Data\Reader\Iterable\FilterHandler\EqualsHandler;
use Yiisoft\Data\Reader\Iterable\FilterHandler\NotHandler;
use Yiisoft\Data\Reader\Iterable\IterableFilterHandlerInterface;
use Yiisoft\Data\Tests\TestCase;

final class NotTest extends TestCase
{
    public static function matchDataProvider(): array
    {
        return [
            [true, [['=', 'value', 44]], ['=' => new EqualsHandler()]],
            [true, [['=', 'value', 46]], ['=' => new EqualsHandler()]],
            [false, [['=', 'value', 45]], ['=' => new EqualsHandler()]],
            [false, [['=', 'value', '45']], ['=' => new EqualsHandler()]],
        ];
    }

    #[DataProvider('matchDataProvider')]
    public function testMatch(bool $expected, array $arguments, array $filterHandlers): void
    {
        $processor = new NotHandler();

        $item = [
            'id' => 1,
            'value' => 45,
        ];

        $this->assertSame($expected, $processor->match($item, $arguments, $filterHandlers));
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

        (new NotHandler())->match(['id' => 1], $arguments, []);
    }

    #[DataProvider('invalidArrayValueDataProvider')]
    public function testMatchFailIfArgumentValueIsNotArray($value): void
    {
        $type = get_debug_type($value);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The values should be array. The $type is received.");

        (new NotHandler())->match(['id' => 1], [$value], []);
    }

    public function testMatchFailIfArgumentValueIsEmptyArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least operator should be provided.');

        (new NotHandler())->match(['id' => 1], [[]], []);
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

        (new NotHandler())->match(['id' => 1], [$filter], []);
    }

    public function testMatchFailForEmptyFilterOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The operator string cannot be empty.');

        (new NotHandler())->match(['id' => 1], [['']], []);
    }

    public function testMatchFailIfFilterOperatorIsNotSupported(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('">" operator is not supported.');

        (new NotHandler())->match(['id' => 1], [['>']], ['=' => new EqualsHandler()]);
    }

    public function testMatchFailIfFilterHandlerIsNotIterable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The filter handler should be an object and implement "%s". The %s is received.',
            IterableFilterHandlerInterface::class,
            stdClass::class,
        ));

        (new NotHandler())->match(['id' => 1], [['=']], ['=' => new stdClass()]);
    }
}
