<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\Reader\Filter\LessThan;

final class CompareTest extends TestCase
{
    public function testWithValue()
    {
        $filter = new LessThan('field', 1);

        $this->assertNotSame($filter, $filter->withValue(1));
        $this->assertSame(2, $filter->withValue(2)->getValue());
    }
}
