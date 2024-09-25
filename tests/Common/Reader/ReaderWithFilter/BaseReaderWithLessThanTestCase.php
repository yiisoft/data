<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;

abstract class BaseReaderWithLessThanTestCase extends BaseReaderTestCase
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new LessThan('balance', 1.1));
        $this->assertFixtures([1], $reader->read());
    }
}
