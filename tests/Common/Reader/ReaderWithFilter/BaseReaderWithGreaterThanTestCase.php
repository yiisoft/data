<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\GreaterThan;

abstract class BaseReaderWithGreaterThanTestCase extends BaseReaderWithFilterTestCase
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new GreaterThan('balance', 499));
        $this->assertFixtures([3], $reader->read());
    }
}
