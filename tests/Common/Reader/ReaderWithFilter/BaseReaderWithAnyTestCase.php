<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\Any;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\Filter\Like;

abstract class BaseReaderWithAnyTestCase extends BaseReaderWithFilterTestCase
{
    public function testWithReader(): void
    {
        $reader = $this
            ->getReader()
            ->withFilter(new Any(new Equals('number', 2), new Equals('number', 3)));
        $this->assertFixtures([1, 2], $reader->read());
    }

    public function testNested(): void
    {
        $reader = $this
            ->getReader()
            ->withFilter(
                new Any(
                    new All(new GreaterThan('balance', 500), new LessThan('number', 5)),
                    new Like('email', 'st'),
                )
            );
        $this->assertFixtures([3, 4], $reader->read());
    }
}
