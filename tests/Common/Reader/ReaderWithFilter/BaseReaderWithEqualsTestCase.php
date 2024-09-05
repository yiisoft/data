<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\Equals;

abstract class BaseReaderWithEqualsTestCase extends BaseReaderWithFilterTestCase
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new Equals('number', 2));
        $this->assertFixtures([1], $reader->read());
    }
}
