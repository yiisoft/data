<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable;

use Yiisoft\Data\Reader\Filter\Equals;
use Yiisoft\Data\Reader\Filter\In;
use Yiisoft\Data\Reader\Iterable\IterableDataProcessor;
use Yiisoft\Data\Tests\TestCase;

final class IterableDataProcessorTest extends TestCase
{
    public function dataBase(): array
    {
        return [
            'empty' => [
                [],
                [],
                new IterableDataProcessor(),
            ],
            'without-filters' => [
                [
                    ['a' => 1],
                    ['a' => 2],
                ],
                [
                    ['a' => 1],
                    ['a' => 2],
                ],
                new IterableDataProcessor(),
            ],
            'filter' => [
                [
                    'a' => ['letter' => 'a', 'number' => 1],
                    'c' => ['letter' => 'c', 'number' => 1],
                ],
                [
                    'a' => ['letter' => 'a', 'number' => 1],
                    'b' => ['letter' => 'b', 'number' => 2],
                    'c' => ['letter' => 'c', 'number' => 1],
                ],
                new IterableDataProcessor(filter: new Equals('number', 1)),
            ],
            'with-filter' => [
                [
                    'a' => ['letter' => 'a', 'number' => 1],
                    'c' => ['letter' => 'c', 'number' => 1],
                ],
                [
                    'a' => ['letter' => 'a', 'number' => 1],
                    'b' => ['letter' => 'b', 'number' => 2],
                    'c' => ['letter' => 'c', 'number' => 1],
                ],
                (new IterableDataProcessor())->withFilter(new Equals('number', 1)),
            ],
            'with-filters' => [
                [
                    'a' => ['letter' => 'a', 'number' => 1],
                    'd' => ['letter' => 'd', 'number' => 1],
                ],
                [
                    'a' => ['letter' => 'a', 'number' => 1],
                    'b' => ['letter' => 'b', 'number' => 2],
                    'c' => ['letter' => 'c', 'number' => 1],
                    'd' => ['letter' => 'd', 'number' => 1],
                ],
                (new IterableDataProcessor())->withFilter(
                    new Equals('number', 1),
                    new In('letter', ['a', 'b', 'd']),
                ),
            ],
        ];
    }

    /**
     * @dataProvider dataBase
     */
    public function testBase(array $expectedData, array $data, IterableDataProcessor $iterableDataProcessor): void
    {
        $this->assertSame(
            $expectedData,
            $iterableDataProcessor->process($data)
        );
    }

    public function testImmutability(): void
    {
        $processor = new IterableDataProcessor();

        $this->assertNotSame($processor, $processor->withFilter());
        $this->assertNotSame($processor, $processor->withSort(null));
    }
}
