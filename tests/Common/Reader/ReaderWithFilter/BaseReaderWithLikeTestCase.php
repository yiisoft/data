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
            'case matches, starts with search string, case sensitive: null' => ['email', 'seed@', null, [2]],
            'case matches, ends with search string, case sensitive: null' => ['email', '@beat', null, [2]],
            'case matches, contains search string, case sensitive: null' => ['email', 'ed@be', null, [2]],
            'case matches, contains search string, case sensitive: true' => ['email', 'ed@be', true, [2]],
            'case matches, contains search string, case sensitive: false' => ['email', 'ed@be', false, [2]],
            'case does not match, contains search string, case sensitive: null' => ['email', 'SEED@', null, [2]],
            'wildcard is not supported' => ['email', '%st', null, []],
        ];
    }

    #[DataProvider('dataWithReader')]
    public function testWithReader(string $field, string $value, bool|null $caseSensitive, array $expectedFixtureIndexes): void
    {
        $reader = $this->getReader()->withFilter(new Like($field, $value, $caseSensitive));
        $this->assertFixtures($expectedFixtureIndexes, $reader->read());
    }
}
