<?php
namespace Yiisoft\Data\Reader;

interface ItemCountAware
{
    public function setItemCount(int $count): void;
}
