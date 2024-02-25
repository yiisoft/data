<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

/**
 * `Any` filter allows combining multiple sub-filters using "or" operator.
 *
 * ```php
 * $dataReader->withFilter(
 *   new Any(
 *     new GreaterThan('id', 88),
 *     new Equals('state', 2),
 *   )
 * );
 * ```
 */
final class Any extends Group
{
}
