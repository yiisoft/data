<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\EqualsNull;

abstract class BaseReaderWithEqualsNullTestCase extends BaseReaderWithFilterTestCase
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new EqualsNull('born_at'));
        $this->assertFixtures(range(0, 3), $reader->read());
    }
}
