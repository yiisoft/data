<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Tests\Common\Reader\BaseReaderTestCase;

abstract class BaseReaderWithLikeTestCase extends BaseReaderTestCase
{
    public static function dataWithReader(): array
    {
        return [
            'case matches, starts with search string' => ['email', 'seed@', [2]],
            'case matches, ends with search string' => ['email', '@beat', [2]],
            'case matches, contains search string' => ['email', 'ed@be', [2]],
            'case does not match' => ['email', 'SEED@', [2]],
            'wildcard is not supported' => ['email', '%st', []],
        ];
    }

    #[DataProvider('dataWithReader')]
    public function testWithReader(string $field, string $value, array $expectedFixtureIndexes): void
    {
        $reader = $this->getReader()->withFilter(new Like($field, $value));
        $this->assertFixtures($expectedFixtureIndexes, $reader->read());
    }
}
