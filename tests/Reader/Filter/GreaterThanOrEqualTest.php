<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use InvalidArgumentException;
use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Reader\FilterDataValidationHelper;
use Yiisoft\Data\Tests\TestCase;

final class GreaterThanOrEqualTest extends TestCase
{
    public function testToArray(): void
    {
        $filter = new GreaterThanOrEqual('test', 1);

        $this->assertSame(['>=', 'test', 1], $filter->toArray());
    }

    /**
     * @dataProvider invalidScalarValueDataProvider
     */
    public function testConstructorFailForInvalidScalarValue($value): void
    {
        $type = FilterDataValidationHelper::getValueType($value);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The value should be scalar. The $type is received.");

        new GreaterThanOrEqual('test', $value);
    }
}
