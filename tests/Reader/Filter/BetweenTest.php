<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use DateTimeInterface;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Between;
use Yiisoft\Data\Tests\TestCase;

use function sprintf;

final class BetweenTest extends TestCase
{
    #[DataProvider('scalarAndDataTimeInterfaceValueDataProvider')]
    public function testToArray($value): void
    {
        $filter = new Between('test', $value, $value);

        $this->assertSame(['between', 'test', $value, $value], $filter->toCriteriaArray());
    }

    #[DataProvider('invalidScalarValueDataProvider')]
    public function testConstructorFailForInvalidFirstValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(sprintf(
            'The value should be scalar or %s instance. The %s is received.',
            DateTimeInterface::class,
            get_debug_type($value),
        ));

        new Between('test', $value, 2);
    }

    #[DataProvider('invalidScalarValueDataProvider')]
    public function testConstructorFailForInvalidSecondValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(sprintf(
            'The value should be scalar or %s instance. The %s is received.',
            DateTimeInterface::class,
            get_debug_type($value),
        ));

        new Between('test', 1, $value);
    }
}
