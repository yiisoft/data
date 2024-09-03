<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;

abstract class BaseReaderWithGreaterThanOrEqualTestCase extends BaseReaderWithFilterTestCase
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new GreaterThanOrEqual('balance', 500));
        $this->assertFixtures([3], $reader->read());
    }
}
