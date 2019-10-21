<?php
declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader;

use Yiisoft\Data\Reader\Iterable\Processor\All;
use Yiisoft\Data\Reader\Iterable\Processor\GreaterThan;
use Yiisoft\Data\Reader\Iterable\Processor\GreaterThanOrEqual;
use Yiisoft\Data\Reader\Iterable\Processor\In;
use Yiisoft\Data\Reader\Iterable\Processor\IterableProcessorInterface;
use Yiisoft\Data\Reader\Iterable\Processor\LessThan;
use Yiisoft\Data\Reader\Iterable\Processor\LessThanOrEqual;
use Yiisoft\Data\Reader\Iterable\Processor\Like;
use Yiisoft\Data\Reader\Iterable\Processor\Not;
use Yiisoft\Data\Tests\TestCase;
use Yiisoft\Data\Reader\Iterable\Processor\Equals;
use Yiisoft\Data\Reader\Iterable\IterableDataReader;
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
                    [$field,] = $arguments;
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

    public function invalidFiltersArrayDataProvider(): array
    {
        return [
            'equalsArgumentsTooSmall' => [new Equals(), ['id'], []],
            'greaterThanArgumentsTooSmall' => [new GreaterThan(), ['id'], []],
            'greaterThanOrEqualArgumentsTooSmall' => [new GreaterThanOrEqual(), ['id'], []],
            'lessThanArgumentsTooSmall' => [new LessThan(), ['id'], []],
            'lessThanOrEqualArgumentsTooSmall' => [new LessThanOrEqual(), ['id'], []],
            'likeArgumentsTooSmall' => [new Like(), ['id'], []],
            'inArgumentsTooSmall' => [new In(), ['id'], []],
            'inValuesNotArray' => [new In(), ['id', false], []],
            'notArgumentsTooSmall' => [new Not(), [], []],
            'notArguments[0]notArray' => [new Not(), [false], []],
            'notArguments[0]tooSmall' => [new Not(), [[]], []],
            'notInvalidOperator' => [new Not(), [['']], ['=' => new Equals()]],
            'groupInvalidArguments' => [new All(), [], []],
            'groupInvalidArgumentsNotArray' => [new All(), [false], []],
            'groupInvalidSubFilter' => [new All(), [[false]], []],
            'groupInvalidSubFilterCountFail' => [new All(), [[[]]], []],
            'groupInvalidSubFilterOperatorFail' => [new All(), [[['']]], []],
        ];
    }

    /**
     * @dataProvider invalidFiltersArrayDataProvider
     */
    public function testInvalidFiltersArray(IterableProcessorInterface $processor, $arguments, array $filterProcessors): void
    {
        $item = $this->getDataSet()[0];
        $this->expectException(\InvalidArgumentException::class);
        $processor->match($item, $arguments, $filterProcessors);
    }
}
