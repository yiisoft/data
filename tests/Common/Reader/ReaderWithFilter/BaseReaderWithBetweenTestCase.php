<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\Between;

abstract class BaseReaderWithBetweenTestCase extends BaseReaderWithFilterTestCase
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new Between('balance', 10.25, 100.0));
        $this->assertFixtures([0, 2, 4], $reader->read());
    }
}
