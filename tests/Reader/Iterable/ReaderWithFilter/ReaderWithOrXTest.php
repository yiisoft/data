<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\AndX;
use Yiisoft\Data\Reader\Filter\OrX;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter\BaseReaderWithOrXTestCase;

final class ReaderWithOrXTest extends BaseReaderWithOrXTestCase
{
    use ReaderTrait;

    public function testNested(): void
    {
        $reader = $this
            ->getReader()
            ->withFilter(
                new OrX(
                    new AndX(new GreaterThan('balance', 500), new LessThan('number', 5)),
                    new Like('email', 'st'),
                ),
            );
        $this->assertFixtures([3, 4], $reader->read());
    }
}
