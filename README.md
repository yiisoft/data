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
[![Build Status](https://travis-ci.com/yiisoft/data.svg?branch=master)](https://travis-ci.com/yiisoft/data)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/data/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/data/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/data/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/data/?branch=master)

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
$dataReader->withFilters((new All())->withFiltersArray([
  ['=', 'id', 88],
  [
    'or' => [
       ['=', 'color', 'red'],
       ['=', 'state', 1],
    ],
  ]
]));
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
direction is set to `desc`. 

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
    ->withLast(13);
```

When displaying first page ID (or another field name to paginate by) of the item displayed last is used with `withLast()`
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
