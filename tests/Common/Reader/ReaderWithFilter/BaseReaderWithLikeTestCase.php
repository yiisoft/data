<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Like;
use Yiisoft\Data\Reader\Filter\LikeMode;
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
            'search: contains, different case, case sensitive: false' => ['email', 'SEED@', false, [2]],
            'wildcard is not supported, %' => ['email', '%st', null, []],
            'wildcard is not supported, _' => ['email', '____@___t', null, []],
            'search: contains backslash' => ['email', 'foo@bar\\baz', null, [0]],
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

    public static function dataWithReaderAndMode(): array
    {
        return [
            // CONTAINS mode tests (should work like before)
            'contains: same case, case sensitive: null' => ['email', 'ed@be', null, LikeMode::CONTAINS, [2]],
            'contains: different case, case sensitive: false' => ['email', 'SEED@', false, LikeMode::CONTAINS, [2]],
            
            // STARTS_WITH mode tests
            'starts with: same case, case sensitive: null' => ['email', 'seed@', null, LikeMode::STARTS_WITH, [2]],
            'starts with: different case, case sensitive: false' => ['email', 'SEED@', false, LikeMode::STARTS_WITH, [2]],
            'starts with: middle part (should fail)' => ['email', 'ed@be', null, LikeMode::STARTS_WITH, []],
            
            // ENDS_WITH mode tests
            'ends with: same case, case sensitive: null' => ['email', '@beat', null, LikeMode::ENDS_WITH, [2]],
            'ends with: different case, case sensitive: false' => ['email', '@BEAT', false, LikeMode::ENDS_WITH, [2]],
            'ends with: middle part (should fail)' => ['email', 'ed@be', null, LikeMode::ENDS_WITH, []],
        ];
    }

    #[DataProvider('dataWithReaderAndMode')]
    public function testWithReaderAndMode(
        string $field,
        string $value,
        bool|null $caseSensitive,
        LikeMode $mode,
        array $expectedFixtureIndexes,
    ): void {
        $reader = $this->getReader()->withFilter(new Like($field, $value, $caseSensitive, $mode));
        $this->assertFixtures($expectedFixtureIndexes, $reader->read());
    }
}
