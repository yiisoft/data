# Yii Data Change Log

## 2.0.0 under development

- New #150: Extract `withLimit()` from `ReadableDataInterface` into `LimitableDataInterface` (@vjik)
- Enh #150: `PaginatorInterface` now extends `ReadableDataInterface` (@vjik)
- Chg #151: Rename `isRequired()` method in `PaginatorInterface` to `isPaginationRequired()` (@vjik)
- New #153, #154: Add `KeysetPaginator::withFilterCallback()` method that allows set closure for preparing filter passed to
  the data reader (@vjik)
- New #153: Add `Compare::withValue()` method (@vjik)
- Chg #154: Raise minimum required PHP version to 8.1 (@vjik)
- Bug #155: Fix `Sort` configuration preparation (@vjik)
- Bug #155: Fix same named order fields in `Sort` were not overriding previous ones (@vjik)
- New #158: Add methods `PaginatorInterface::isSortable()` and `PaginatorInterface::withSort()` (@vjik)
- New #164: Add methods `PaginatorInterface::isFilterable()` and `PaginatorInterface::withFilter()` (@vjik)
- Chg #159: Replace `withNextPageToken()` and `withPreviousPageToken()` of `PaginatorInterface` with `withToken()`,
  `getNextPageToken()` with `getNextToken()`, `getPreviousPageToken()` with `getPreviousToken()`, and add `getToken()`.
  These methods use new `PageToken` class (@vjik)
- New #160: Add `Sort::hasFieldInConfig()` method that checks for the presence of a field in the sort config (@vjik)
- New #161: Add `PageNotFoundException` (@vjik)
- Chg #161: `PaginatorInterface::getCurrentPageSize()` returns 0 instead throws exception if page specified is 
  not found (@vjik)
- Enh #161: Add more specified psalm annotations to `CountableDataInterface::count()`,
  `PaginatorInterface::getCurrentPageSize()` and `OffsetPaginator::getTotalItems()` (@vjik)
- Chg #166: Remove `EqualsEmpty` filter (@vjik)

## 1.0.1 January 25, 2023

- Chg #137: In `FilterableDataInterface::withFilterHandlers()` rename parameter `$iterableFilterHandlers` to
  `$filterHandlers` (@vjik)

## 1.0.0 January 14, 2023

- Initial release.
