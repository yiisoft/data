<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

final class PageNotFoundException extends PaginatorException
{
    public function __construct()
    {
        parent::__construct('Page not found.');
    }
}
