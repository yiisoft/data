<?php
namespace Yiisoft\Data\Tests;

use Yiisoft\Data\Sort;

class CustomSort extends Sort
{
    protected function parseSortParam(array $parameters): array
    {
        $attributes = [];
        foreach ($parameters as $item) {
            $attributes[] = ($item['dir'] === 'desc') ? '-' . $item['field'] : $item['field'];
        }

        return $attributes;
    }
}
