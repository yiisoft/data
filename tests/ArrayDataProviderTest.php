<?php

namespace Yiisoft\Data\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Data\ArrayDataProvider;
use Yiisoft\Data\Pagination;
use Yiisoft\Data\Sort;

/**
 * @group data
 */
class ArrayDataProviderTest extends TestCase
{
    public function testGetModels(): void
    {
        $simpleArray = [
            ['name' => 'zero'],
            ['name' => 'one'],
        ];
        $dataProvider = new ArrayDataProvider();
        $dataProvider->allModels = $simpleArray;
        $this->assertEquals($simpleArray, $dataProvider->getModels());
    }

    public function testGetSortedData(): void
    {
        $simpleArray = [['sortField' => 1], ['sortField' => 0]];
        $dataProvider = new ArrayDataProvider();
        $dataProvider->allModels = $simpleArray;

        $sort = new Sort();
        $sort->attributes = [
            'sort' => [
                'asc' => ['sortField' => SORT_ASC],
                'desc' => ['sortField' => SORT_DESC],
                'label' => 'Sorting',
                'default' => 'asc',
            ],
        ];

        $sort->defaultOrder = [
            'sort' => SORT_ASC,
        ];

        $dataProvider->setSort($sort);

        $sortedArray = [['sortField' => 0], ['sortField' => 1]];
        $this->assertEquals($sortedArray, $dataProvider->getModels());
    }

    public function testGetSortedDataByInnerArrayField(): void
    {
        $sort = new Sort()

        $simpleArray = [
            ['innerArray' => ['sortField' => 1]],
            ['innerArray' => ['sortField' => 0]],
        ];
        $dataProvider = new ArrayDataProvider();
        $dataProvider->allModels = $simpleArray;
        $dataProvider->setSort($sort);

        = [
        'attributes' => [
            'sort' => [
                'asc' => ['innerArray.sortField' => SORT_ASC],
                'desc' => ['innerArray.sortField' => SORT_DESC],
                'label' => 'Sorting',
                'default' => 'asc',
            ],
        ],
        'defaultOrder' => [
            'sort' => SORT_ASC,
        ],
    ];
        $sortedArray = [
            ['innerArray' => ['sortField' => 0]],
            ['innerArray' => ['sortField' => 1]],
        ];
        $this->assertEquals($sortedArray, $dataProvider->getModels());
    }

    public function testCaseSensitiveSort(): void
    {
        // source data
        $unsortedProjects = [
            ['title' => 'Zabbix', 'license' => 'GPL'],
            ['title' => 'munin', 'license' => 'GPL'],
            ['title' => 'Arch Linux', 'license' => 'GPL'],
            ['title' => 'Nagios', 'license' => 'GPL'],
            ['title' => 'zend framework', 'license' => 'BSD'],
            ['title' => 'Zope', 'license' => 'ZPL'],
            ['title' => 'active-record', 'license' => false],
            ['title' => 'ActiveState', 'license' => false],
            ['title' => 'mach', 'license' => false],
            ['title' => 'MySQL', 'license' => 'GPL'],
            ['title' => 'mssql', 'license' => 'EULA'],
            ['title' => 'Master-Master', 'license' => false],
            ['title' => 'Zend Engine', 'license' => false],
            ['title' => 'Mageia Linux', 'license' => 'GNU GPL'],
            ['title' => 'nginx', 'license' => 'BSD'],
            ['title' => 'Mozilla Firefox', 'license' => 'MPL'],
        ];

        // expected data
        $sortedProjects = [
            // upper cased titles
            ['title' => 'ActiveState', 'license' => false],
            ['title' => 'Arch Linux', 'license' => 'GPL'],
            ['title' => 'Mageia Linux', 'license' => 'GNU GPL'],
            ['title' => 'Master-Master', 'license' => false],
            ['title' => 'Mozilla Firefox', 'license' => 'MPL'],
            ['title' => 'MySQL', 'license' => 'GPL'],
            ['title' => 'Nagios', 'license' => 'GPL'],
            ['title' => 'Zabbix', 'license' => 'GPL'],
            ['title' => 'Zend Engine', 'license' => false],
            ['title' => 'Zope', 'license' => 'ZPL'],
            // lower cased titles
            ['title' => 'active-record', 'license' => false],
            ['title' => 'mach', 'license' => false],
            ['title' => 'mssql', 'license' => 'EULA'],
            ['title' => 'munin', 'license' => 'GPL'],
            ['title' => 'nginx', 'license' => 'BSD'],
            ['title' => 'zend framework', 'license' => 'BSD'],
        ];

        $dataProvider = new ArrayDataProvider();
        $dataProvider->allModels = $unsortedProjects;
        $dataProvider->sort = [
            'attributes' => [
                'sort' => [
                    'asc' => ['title' => SORT_ASC],
                    'desc' => ['title' => SORT_DESC],
                    'label' => 'Title',
                    'default' => 'desc',
                ],
            ],
            'defaultOrder' => [
                'sort' => SORT_ASC,
            ],
        ];

        $this->assertEquals($sortedProjects, $dataProvider->getModels());
    }

    public function testGetKeys(): void
    {
        $pagination = new Pagination();
        $pagination->setPageSize(2);

        $simpleArray = [
            ['name' => 'zero'],
            ['name' => 'one'],
            ['name' => 'tow'],
        ];
        $dataProvider = new ArrayDataProvider();
        $dataProvider->allModels = $simpleArray;
        $dataProvider->setPagination($pagination);
        $this->assertEquals([0, 1], $dataProvider->getKeys());

        $namedArray = [
            'key1' => ['name' => 'zero'],
            'key2' => ['name' => 'one'],
            'key3' => ['name' => 'two'],
        ];
        $dataProvider = new ArrayDataProvider();
        $dataProvider->allModels = $namedArray;
        $dataProvider->setPagination($pagination);
        $this->assertEquals(['key1', 'key2'], $dataProvider->getKeys());

        $mixedArray = [
            'key1' => ['name' => 'zero'],
            9 => ['name' => 'one'],
            'key3' => ['name' => 'two'],
        ];
        $dataProvider = new ArrayDataProvider();
        $dataProvider->allModels = $mixedArray;
        $dataProvider->setPagination($pagination);
        $this->assertEquals(['key1', 9], $dataProvider->getKeys());
    }
}
