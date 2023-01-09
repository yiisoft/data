<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use Yiisoft\Data\Reader\Filter\EqualsNull;
use Yiisoft\Data\Tests\TestCase;

final class EqualsNullTest extends TestCase
{
    public function testToArray(): void
    {
        $filter = new EqualsNull('test');

        $this->assertSame(['null', 'test'], $filter->toCriteriaArray());
    }
}
