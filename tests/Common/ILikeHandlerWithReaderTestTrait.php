<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Common;

use PHPUnit\Framework\Attributes\DataProvider;
use Yiisoft\Data\Reader\Filter\ILike;

trait ILikeHandlerWithReaderTestTrait
{
    public static function dataWithReader(): array
    {
        return [
            'case matches' => ['email', 'seed@', [2]],
            'case does not match' => ['email', 'SEED@', [2]],
        ];
    }

    #[DataProvider('dataWithReader')]
    public function testWithReader(string $field, string $value, array $expectedFixtureIndexes): void
    {
        $reader = $this->getReader()->withFilter(new ILike($field, $value));
        $this->assertFixtures($expectedFixtureIndexes, $reader->read());
    }
}
