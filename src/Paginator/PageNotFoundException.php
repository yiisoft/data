<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

final class PageNotFoundException extends PaginatorException
{
    public function __construct(string $message = 'Page not found.')
    {
        parent::__construct($message);
    }
}
