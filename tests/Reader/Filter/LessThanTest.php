<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use DateTimeInterface;
use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Tests\TestCase;

use function sprintf;

final class LessThanTest extends TestCase
{
    /**
     * @dataProvider scalarAndDataTimeInterfaceValueDataProvider
     */
    public function testToArray($value): void
    {
        $filter = new LessThan('test', $value);

        $this->assertSame(['<', 'test', $value], $filter->toCriteriaArray());
    }

    /**
     * @dataProvider invalidScalarValueDataProvider
     */
    public function testConstructorFailForInvalidScalarValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(sprintf(
            'The value should be scalar or %s instance. The %s is received.',
            DateTimeInterface::class,
            get_debug_type($value),
        ));

        new LessThan('test', $value);
    }
}
