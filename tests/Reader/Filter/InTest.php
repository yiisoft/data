<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Data\Tests\TestCase;

final class InTest extends TestCase
{
    public function testToArray(): void
    {
        $filter = new In('test', [1, 2]);

        $this->assertSame(['in', 'test', [1, 2]], $filter->toCriteriaArray());
    }

    #[DataProvider('invalidScalarValueDataProvider')]
    public function testConstructorFailForInvalidScalarValue($value): void
    {
        $type = get_debug_type($value);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The value should be scalar. The $type is received.");

        new In('test', [$value]);
    }
}
