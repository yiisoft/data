<?php

declare(strict_types=1);

namespace Yiisoft\Data\Reader\Filter;

/**
 * Like filter matching modes.
 */
enum LikeMode
{
    /**
     * Field value contains the search value.
     */
    case Contains;

    /**
     * Field value starts with the search value.
     */
    case StartsWith;

    /**
     * Field value ends with the search value.
     */
    case EndsWith;
}
