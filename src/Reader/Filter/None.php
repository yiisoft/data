<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

use Yiisoft\Data\Reader\FilterInterface;

/**
 * Represents a filter that matches no items.
 * Used to indicate that all items should be excluded.
 */
final class None implements FilterInterface {}
