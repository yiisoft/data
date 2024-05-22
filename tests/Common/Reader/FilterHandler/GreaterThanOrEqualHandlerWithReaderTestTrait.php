<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\FilterHandler;

use Yiisoft\Data\Reader\Filter\GreaterThanOrEqual;

trait GreaterThanOrEqualHandlerWithReaderTestTrait
{
    public function testWithReader(): void
    {
        $reader = $this->getReader()->withFilter(new GreaterThanOrEqual('balance', 500));
        $this->assertFixtures([3], $reader->read());
    }
}
