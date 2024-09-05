<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\In;

abstract class BaseReaderWithInTestCase extends BaseReaderWithFilterTestCase
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new In('number', [2, 3]));
        $this->assertFixtures([1, 2], $reader->read());
    }
}
