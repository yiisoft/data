<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\AndX;
use Yiisoft\Data\Reader\Filter\OrX;
use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;

abstract class BaseReaderWithOrXTestCase extends BaseReaderTestCase
{
    public function testWithReader(): void
    {
        $reader = $this
            ->getReader()
            ->withFilter(new OrX(new Equals('number', 2), new Equals('number', 3)));
        $this->assertFixtures([1, 2], $reader->read());
    }

    public function testNested(): void
    {
        $reader = $this
            ->getReader()
            ->withFilter(
                new OrX(
                    new AndX(new GreaterThan('balance', 500), new LessThan('number', 5)),
                    new Like('email', 'st'),
                )
            );
        $this->assertFixtures([3, 4], $reader->read());
    }
}
