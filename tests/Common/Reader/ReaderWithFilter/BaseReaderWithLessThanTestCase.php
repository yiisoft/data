<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\LessThan;

abstract class BaseReaderWithLessThanTestCase extends BaseReaderWithFilterTestCase
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new LessThan('balance', 1.1));
        $this->assertFixtures([1], $reader->read());
    }
}
