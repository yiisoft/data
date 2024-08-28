<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common\Reader\FilterHandler;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\Like;

trait LikeHandlerWithReaderTestTrait
{
    public static function dataWithReader(): array
    {
        return [
            'case matches' => ['email', 'seed@', [2]],
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
