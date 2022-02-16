<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Filter;

use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Tests\TestCase;

final class LikeTest extends TestCase
{
    public function testToArray(): void
    {
        $filter = new Like('test', 'value');

        $this->assertSame(['like', 'test', 'value'], $filter->toArray());
    }
}
