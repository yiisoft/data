<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\Filter\Not;
use Yiisoft\Data\Tests\TestCase;

final class NotTest extends TestCase
{
    public function testToArray(): void
    {
        $filter = new Not(new Like('test', 'value'));

        $this->assertSame(['not', ['like', 'test', 'value']], $filter->toArray());
    }
}
