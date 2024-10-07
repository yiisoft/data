<?php

declare(strict_types=1);

namespace Yiisoft\Data\Tests\Reader\Iterable\ReaderWithFilter;

use Yiisoft\Data\Tests\Common\Reader\ReaderWithFilter\BaseReaderWithLikeTestCase;

final class ReaderWithLikeTest extends BaseReaderWithLikeTestCase
{
    use ReaderTrait;

    public static function dataWithReader(): array
    {
        $data = parent::dataWithReader();
        $data['search: contains, different case, case sensitive: null'] = ['email', 'SEED@', null, [2]];
        $data['search: contains, different case, case sensitive: true'] = ['email', 'SEED@', true, []];

        return $data;
    }
}
