<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Filter\LessThan;

use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertSame;

final class CompareTest extends TestCase
{
    public function testWithValue(): void
    {
        $sourceFilter = new LessThan('field', 1);

        $filter = $sourceFilter->withValue(2);

        assertNotSame($sourceFilter, $filter);
        assertSame('field', $filter->field);
        assertSame(2, $filter->value);
    }
}
