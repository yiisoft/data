<?php

declare(strict_types=1);

namespace Yiisoft\Data\Paginator;

interface OffsetPaginatorInterface extends PaginatorInterface
{
    public function getCurrentPage(): int;
    public function getTotalItems(): int;
    public function getOffset(): int;
    public function getTotalPages(): int;
    public function withCurrentPage(int $num): self;
}
