<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\FilterHandler;

use Yiisoft\Data\Reader\Filter\Equals;

abstract class BaseEqualsHandlerWithReaderTest extends BaseFilterWithReaderTest
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new Equals('number', 2));
        $this->assertFixtures([1], $reader->read());
    }
}
