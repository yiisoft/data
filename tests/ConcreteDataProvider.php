<?php
namespace Yiisoft\Data\Tests;

use Yiisoft\Data\BaseDataProvider;

class ConcreteDataProvider extends BaseDataProvider
{
    protected function prepareModels(): array
    {
        return [];
    }

    protected function prepareKeys(array $models): array
    {
        return [];
    }

    protected function prepareTotalCount(): int
    {
        return 0;
    }
}
