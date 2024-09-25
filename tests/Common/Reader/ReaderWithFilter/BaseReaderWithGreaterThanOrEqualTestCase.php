<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;

abstract class BaseReaderWithGreaterThanOrEqualTestCase extends BaseReaderTestCase
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new GreaterThanOrEqual('balance', 500));
        $this->assertFixtures([3], $reader->read());
    }
}
