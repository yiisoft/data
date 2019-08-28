<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii Data</h1>
    <br>
</p>

The package provides generic data abstractions. The aim is to hide stoarge aspect from the operations of reading data,
writing data and processing data.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/data/v/stable.png)](https://packagist.org/packages/yiisoft/data)
[![Total Downloads](https://poser.pugx.org/yiisoft/data/downloads.png)](https://packagist.org/packages/yiisoft/data)
[![Build Status](https://travis-ci.com/yiisoft/data.svg?branch=master)](https://travis-ci.com/yiisoft/data)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/data/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/data/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/data/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/data/?branch=master)

## Concepts

Each data consists of items. Each item has multiple fields and has a key.

The library provides interfaces for reading data, writing data and processing data.

## Reading data

## Pagination

Pagination allows to obtain a limited subset of data that is both handy for displaying items page by page and for getting
acceptable performance on big data sets.

There are two types of pagination provided: traditional offset pagination and keyset pagination.

### Offset pagination

Offset pagination is a common pagination method that selects OFFSET + LIMIT items and then skips OFFSET items.  

Advantages:

- Total number of pages is available
- Can get to specific page
- Any order could be set by user including fields to order by

Disadvantages:

- Performance degrades with page number increase
- Insertions or deletions in the middle of the data are making results inconsistent

### Keyset pagination

Keyset pagination is alternative pagination method that is good for infinite scrolling and "load more". It is selecting
LIMIT items there are greater or lesser than key value specified. 

Advantages:

- Performance does not depend on page number
- Consistent results regardless of insertions and deletions

Disadvantages:

- Total number of pages is not available
- Can not get to specific page, only "previous" and "next"
- User can only set direction of the order, not fields

## Writing data

## Processing data


