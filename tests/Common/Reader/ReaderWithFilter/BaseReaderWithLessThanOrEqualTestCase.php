<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\LessThanOrEqual;

abstract class BaseReaderWithLessThanOrEqualTestCase extends BaseReaderWithFilterTestCase
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new LessThanOrEqual('balance', 1.0));
        $this->assertFixtures([1], $reader->read());
    }
}
