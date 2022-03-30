<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use DateTimeInterface;
use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\Between;
use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Tests\TestCase;

use function sprintf;

final class BetweenTest extends TestCase
{
    /**
     * @dataProvider scalarAndDataTimeInterfaceValueDataProvider
     */
    public function testToArray($value): void
    {
        $filter = new Between('test', $value, $value);

        $this->assertSame(['between', 'test', $value, $value], $filter->toArray());
    }

    /**
     * @dataProvider invalidScalarValueDataProvider
     */
    public function testConstructorFailForInvalidFirstValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(sprintf(
            'The value should be scalar or %s instance. The %s is received.',
            DateTimeInterface::class,
            FilterDataValidationHelper::getValueType($value),
        ));

        new Between('test', $value, 2);
    }

    /**
     * @dataProvider invalidScalarValueDataProvider
     */
    public function testConstructorFailForInvalidSecondValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(sprintf(
            'The value should be scalar or %s instance. The %s is received.',
            DateTimeInterface::class,
            FilterDataValidationHelper::getValueType($value),
        ));

        new Between('test', 1, $value);
    }
}
