<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\ReaderWithFilter;

use Yiisoft\Data\Reader\Filter\All;
use Yiisoft\Data\Reader\Filter\Any;
use Yiisoft\Data\Reader\Filter\GreaterThan;
use Yiisoft\Data\Reader\Filter\LessThan;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter\BaseReaderWithAnyTestCase;

final class ReaderWithAnyTest extends BaseReaderWithAnyTestCase
{
    use ReaderTrait;

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
