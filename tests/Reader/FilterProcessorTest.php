<?php
declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader;

use Yiisoft\Data\Tests\TestCase;
use Yiisoft\Data\Reader\Filter\Processor\Iterable\Equals;
use Yiisoft\Data\Reader\IterableDataReader;
use Yiisoft\Data\Reader\Sort;

class FilterProcessorTest extends TestCase
{

    private function getDataSet(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Codename Boris',
            ],
            [
                'id' => 2,
                'name' => 'Codename Doris',
            ],
            [
                'id' => 3,
                'name' => 'Agent K',
            ],
            [
                'id' => 5,
                'name' => 'Agent J',
            ],
            [
                'id' => 6,
                'name' => '007',
            ],
        ];
    }

    public function testCustomEquals(): void
    {
        $sort = (new Sort(['id', 'name']))->withOrderString('id');

        $dataReader = (new IterableDataReader($this->getDataSet()))
            ->withSort($sort)
            ->withFilterProcessors(new class extends Equals {
                public function match(array $item, array $arguments, array $filterUnits): bool
                {
                    [$field, ] = $arguments;
                    if ($item[$field] === 2) {
                        return true;
                    }
                    return parent::match($item, $arguments, $filterUnits);
                }
            });
        $dataReader = $dataReader->withFilter(new \Yiisoft\Data\Reader\Filter\Equals('id', 100));

        $expected = [
            [
                'id' => 2,
                'name' => 'Codename Doris',
            ]
        ];

        $this->assertSame($expected, $this->iterableToArray($dataReader->read()));
    }
}
