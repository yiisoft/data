<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;

abstract class BaseReaderWithEqualsTestCase extends BaseReaderTestCase
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new Equals('number', 2));
        $this->assertFixtures([1], $reader->read());
    }
}
