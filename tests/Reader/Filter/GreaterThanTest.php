<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use DateTimeInterface;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Tests\TestCase;

use function sprintf;

final class GreaterThanTest extends TestCase
{
    #[DataProvider('scalarAndDataTimeInterfaceValueDataProvider')]
    public function testToArray($value): void
    {
        $filter = new GreaterThan('test', $value);

        $this->assertSame(['>', 'test', $value], $filter->toCriteriaArray());
    }

    #[DataProvider('invalidScalarValueDataProvider')]
    public function testConstructorFailForInvalidScalarValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(sprintf(
            'The value should be scalar or %s instance. The %s is received.',
            DateTimeInterface::class,
            get_debug_type($value),
        ));

        new GreaterThan('test', $value);
    }
}
