<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

/**
 * `All` filter allows combining multiple sub-filters using "and" operator.
 *
 * ```php
 * $dataReader->withFilter(
 *   new All(
 *     new GreaterThan('id', 88),
 *     new Equals('state', 2),
 *     new Like('name', 'eva'),
 *   )
 * );
 * ```
 */
final class All extends Group
{
}
