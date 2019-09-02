<?php


namespace Yiisoft\Data\Tests\Reader;


use Yiisoft\Data\Reader\Criterion\AndAll;
use Yiisoft\Data\Reader\Criterion\Compare;
use Yiisoft\Data\Reader\Criterion\GreaterThan;
use Yiisoft\Data\Reader\Criterion\LessThan;
use Yiisoft\Data\Reader\Criterion\OrAny;
use Yiisoft\Data\Tests\TestCase;

class CriteriaTest extends TestCase
{
    public function testIt(): void
    {
        $criteria = new AndAll(
            new Compare('test', 42),
            new Compare('test2', 34),
            new OrAny(
                new LessThan('temperature', 10),
                new GreaterThan('temperature', 30)
            )
        );
        $this->assertSame([], $criteria->toArray());
    }
}
