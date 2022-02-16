<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use Yiisoft\Data\Reader\Filter\EqualsEmpty;
use Yiisoft\Data\Tests\TestCase;

final class EqualsEmptyTest extends TestCase
{
    public function testToArray(): void
    {
        $filter = new EqualsEmpty('test');

        $this->assertSame(['empty', 'test'], $filter->toArray());
    }
}
