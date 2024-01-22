# Yii Data Change Log

## 2.0.0 under development

- New #150: Extract `withLimit()` from `ReadableDataInterface` into `LimitableDataInterface` (@vjik)
- Enh #150: `PaginatorInterface` now extends `ReadableDataInterface` (@vjik)
- Chg #151: Rename `isRequired()` method in `PaginatorInterface` to `isPaginationRequired()` (@vjik)
- New #153, #154: Add `KeysetPaginator::withFilterCallback()` method that allows set closure for preparing filter passed to
  the data reader (@vjik)
- New #153: Add `Compare::withValue()` method (@vjik)
- Chg #154: Raise minimum required PHP version to 8.1 (@vjik)
- Fix #155: Fix `Sort` configuration preparation (@vjik)
- Chg #155: Same order fields in current order in `Sort` override previous now (@vjik)

## 1.0.1 January 25, 2023

- Chg #137: In `FilterableDataInterface::withFilterHandlers()` rename parameter `$iterableFilterHandlers` to
  `$filterHandlers` (@vjik)

## 1.0.0 January 14, 2023

- Initial release.
