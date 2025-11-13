<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterInterface;

/**
 * Represents a filter that matches all items.
 * Used to indicate that no filtering should be applied.
 */
final class All implements FilterInterface {}
