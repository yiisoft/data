<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii Data</h1>
    <br>
</p>

The package provides generic data abstractions. The aim is to hide stoarge aspect from the operations of reading,
writing and processing data.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/data/v/stable.png)](https://packagist.org/packages/yiisoft/data)
[![Total Downloads](https://poser.pugx.org/yiisoft/data/downloads.png)](https://packagist.org/packages/yiisoft/data)
[![Build status](https://github.com/yiisoft/data/workflows/build/badge.svg)](https://github.com/yiisoft/data/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/data/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/data/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/data/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/data/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fdata%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/data/master)
[![static analysis](https://github.com/yiisoft/data/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/data/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/data/coverage.svg)](https://shepherd.dev/github/yiisoft/data)

## Concepts

Each data consists of items. Each item has multiple named fields. All items in a data set have the same structure.

The library provides interfaces for reading, writing and processing such data sets.

## Reading data

Data reader aim is to read data from a storage such as database, array or API and convert it to simple array of
field => value items.

```php
$reader = new MyDataReader(...);
$result = $reader->read(); 
```

Note that result is `iterable` so you can use `foreach` on it but need to prepare it if you need to use it as array:

```php
// using is foreach
foreach ($result as $item) {
    // ...
}

// preparing array
$dataArray = $result instanceof \Traversable ? iterator_to_array($result, true) : (array)$result;
```

Number of items returned can be limited:

```php
$reader = (new MyDataReader(...))->withLimit(10);
```

### Counting total number of items

In order to know total number of items in a data provider implementing `CountableDataInterface`:

```php
$reader = new MyDataReader(...);
$total = count($reader);
```

### Filtering

In order to filter data in a data provider implementing `FilterableDataInterface` you need to supply filter to
`withFilter()` method:

```php
$filter = new All(
    new GreaterThan('id', 3),
    new Like('name', 'agent')
);

$reader = (new MyDataReader(...))
    ->withFilter($filter);

$data = $reader->read();
```

Filter could be composed with:

- `All`
- `Any`
- `Equals`
- `GreaterThan`
- `GreaterThanOrEqual`
- `In`
- `LessThan`
- `LessThanOrEqual`
- `Like`
- `Not`

#### Filtering with arrays

The `All` and` Any` filters have a `withFiltersArray()` method, which allows you to define filters with arrays.

```php
$dataReader->withFilter((new All())->withFiltersArray([
  ['=', 'id', 88],
  [
    'or',
    [
      ['=', 'color', 'red'],
      ['=', 'state', 1],
    ]
  ]
]));
```

#### Implementing your own filter

In order to have your own filter:
- Implement at least `FilterInterface`, which includes:
  - `getOperator()` method that returns a string that represents a filter operation
  - `toArray()` method that returns an array with filtering parameters.
- If you want to create a filter processor for a specific data reader type, then you need to implement at least 
`FilterProcessorInterface`. It has a single `getOperator()` method that returns a string representing a filter operation.
In addition, each data reader specifies an extended interface required for processing or building the operation.
*For example, `IterableDataFilter` defines `IterableProcessorInterface`, which contains additional `match()` 
method to execute a filter on PHP variables.*

You can add your own filter processors to the data reader using the `withFilterProcessors()` method. You can add any filter
processor to Reader. If reader is not able to use a filter, filter is ignored.

```php
// own filter for filtering
class OwnNotTwoFilter implenents FilterInterface
{
    private $field;

    public function __construct($field)
    {
        $this->field = $field;
    }
    public static function getOperator(): string
    {
        return 'my!2';
    }
    public function toArray(): array
    {
        return [static::getOperator(), $this->field];
    }
}

// own iterable filter processor for matching
class OwnIterableNotTwoFilterProcessor implements 
{
    public function getOperator(): string
    {
        return OwnNotTwoFilter::getOperator();
    }

    public function match(array $item, array $arguments, array $filterProcessors): bool
    {
        [$field] = $arguments;
        return $item[$field] != 2;
    }
}

// and using it on a data reader
$filter = new All(
    new LessThan('id', 8),
    new OwnNotTwoFilter('id'),
);

$reader = (new MyDataReader(...))
    ->withFilter($filter)
    ->withFilterProcessors(
        new OwnIterableNotTwoFilterProcessor()
        new OwnSqlNotTwoFilterProcessor()    // for SQL
        // and for any supported readers...
    );

$data = $reader->read();
```

### Sorting

In order to sort data in a data provider implementing `SortableDataInterface` you need to supply sort object to
`sortFilter()` method:

```php
$sorting = new Sort([
    'id',
    'name'
]);

$sorting = $sorting->withOrder(['name' => 'asc']);
// or $sorting = $sorting->withOrderString('name');

$reader = (new MyDataReader(...))
    ->withSort($sorting);

$data = $reader->read();
```

In sorting constructor you set which fields should be order-able and, optionally, details on how these should be ordered.
The order to apply is specified via `withOrder()` where you supply an array with keys correspond to field names
and values correspond to order (`asc` or `desc`). Alternatively `withOrderString()` can be used. In this case
ordering is represented as a single string containing comma separate field names. If name is prefixed by `-`, ordering
direction is set to `desc`. You can set default sort if you are not set order statement using `withDefaultOrder()`. 

### Skipping some items

In case you need to skip some items from the beginning of data reader implementing `OffsetableDataInterface`:

```php
$reader = (new MyDataReader(...))->withOffset(10);
```

### Implementing your own data reader

In order to have your own data reader you need to implement at least `DataReaderInteface`. It has a single `read()`
method that returns iterable representing a set of items.

Additional interfaces could be implemented in order to support different pagination types, ordering and filtering:

- `CountableDataInterface` - allows getting total number of items in data provider.
- `FilterableDataInterface` - allows returning subset of items based on criteria.
- `SortableDataInterface` - allows sorting by one or multiple fields.
- `OffsetableDataInterface` - allows to skip first N items when reading data.

Note that when implementing these, methods, instead of modifying data, should only define criteria that is later used
in `read()` to affect what data is returned.

## Pagination

Pagination allows to obtain a limited subset of data that is both handy for displaying items page by page and for getting
acceptable performance on big data sets.

There are two types of pagination provided: traditional offset pagination and keyset pagination.

### Offset pagination

Offset pagination is a common pagination method that selects OFFSET + LIMIT items and then skips OFFSET items.  

Advantages:

- Total number of pages is available
- Can get to specific page
- Data can be unordered

Disadvantages:

- Performance degrades with page number increase
- Insertions or deletions in the middle of the data are making results inconsistent

Usage is the following:

```php
$reader = (new MyDataReader(...));

$paginator = (new OffsetPaginator($dataReader))
            ->withPageSize(10)
            ->withCurrentPage(2);


$total = $paginator->getTotalPages();
$data = $paginator->read();
```

### Keyset pagination

Keyset pagination is alternative pagination method that is good for infinite scrolling and "load more". It is selecting
LIMIT items that have key field greater or lesser (depending on the sorting) than value specified. 

Advantages:

- Performance does not depend on page number
- Consistent results regardless of insertions and deletions

Disadvantages:

- Total number of pages is not available
- Can not get to specific page, only "previous" and "next"
- Data cannot be unordered

Usage is the following:

```php
$sort = (new Sort(['id', 'name']))->withOrderString('id');

$dataReader = (new MyDataReader(...))
    ->withSort($sort);

$paginator = (new KeysetPaginator($dataReader))
    ->withPageSize(10)
    ->withNextPageToken('13');
```

When displaying first page ID (or another field name to paginate by) of the item displayed last is used with `withNextPageToken()`
to obtain next page.   

## Writing data

```php
$writer = new MyDataWriter(...);
$writer->write($arrayOfItems);
```

## Processing data

```php
$processor = new MyDataProcessor(...);
$processor->process($arrayOfItems);
```

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```shell
./vendor/bin/infection
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

### Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

### Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)

## License

The Yii Data is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).
