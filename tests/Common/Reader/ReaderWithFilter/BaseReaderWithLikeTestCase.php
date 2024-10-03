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
            'search: starts with, same case, case sensitive: null' => ['email', 'seed@', null, [2]],
            'search: ends with, same case, case sensitive: null' => ['email', '@beat', null, [2]],
            'search: contains, same case, case sensitive: null' => ['email', 'ed@be', null, [2]],
            'search: contains, same case, case sensitive: false' => ['email', 'ed@be', false, [2]],
            'search: contains, same case, case sensitive: true' => ['email', 'ed@be', true, [2]],
            'search: contains, different case, case sensitive: false' => ['email', 'SEED@', false, [2]],
            'search: contains, different case, case sensitive: true' => ['email', 'SEED@', true, []],
            'wildcard is not supported' => ['email', '%st', null, []],
        ];
    }

    #[DataProvider('dataWithReader')]
    public function testWithReader(
        string $field,
        string $value,
        bool|null $caseSensitive,
        array $expectedFixtureIndexes,
    ): void {
        $reader = $this->getReader()->withFilter(new Like($field, $value, $caseSensitive));
        $this->assertFixtures($expectedFixtureIndexes, $reader->read());
    }
}
