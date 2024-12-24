<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

use function sprintf;

final class PageNotFoundException extends PaginatorException
{
    public function __construct(int $page)
    {
        parent::__construct(
            sprintf('Page %d not found.', $page)
        );
    }
}
